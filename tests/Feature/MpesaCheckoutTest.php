<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Mpesa\MpesaGateway;
use App\Services\Mpesa\PhoneNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MpesaCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-callback-secret';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        config([
            'app.url' => 'https://tixswap.test',
            'services.mpesa' => [
                'driver' => 'daraja',
                'environment' => 'sandbox',
                'consumer_key' => 'key',
                'consumer_secret' => 'secret',
                'shortcode' => '174379',
                'passkey' => 'passkey',
                'transaction_type' => 'CustomerPayBillOnline',
                'party_b' => null,
                'callback_base_url' => null,
                'callback_secret' => self::SECRET,
            ],
            'services.tixswap.commission_percent' => 10,
            'services.tixswap.reservation_minutes' => 10,
            'services.tixswap.escrow_release_hours_after_event' => 24,
        ]);
    }

    private function fakeDaraja(array $queryResponse = ['errorCode' => '500.001.1001', 'errorMessage' => 'The transaction is being processed'], int $queryStatus = 500, array|int $pushResponse = []): void
    {
        // Each STK push gets its own CheckoutRequestID, like the real API:
        // ws_CO_123 for the first, ws_CO_456 for the second.
        $accepted = fn (string $id) => Http::response($pushResponse + [
            'MerchantRequestID' => 'M-'.$id,
            'CheckoutRequestID' => $id,
            'ResponseCode' => '0',
            'ResponseDescription' => 'Success. Request accepted for processing',
            'CustomerMessage' => 'Success. Request accepted for processing',
        ]);
        $push = is_int($pushResponse)
            ? Http::response(['errorMessage' => 'Bad Request'], $pushResponse)
            : Http::sequence()->pushResponse($accepted('ws_CO_123'))->pushResponse($accepted('ws_CO_456'));

        Http::fake([
            'sandbox.safaricom.co.ke/oauth/*' => Http::response(['access_token' => 'tok', 'expires_in' => '3599']),
            'sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' => $push,
            'sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query' => Http::response($queryResponse, $queryStatus),
        ]);
    }

    private function startCheckout(?User $buyer = null, ?Ticket $ticket = null): array
    {
        $buyer ??= User::factory()->create();
        $ticket ??= Ticket::factory()->create(['price' => 2499.50]);

        $this->actingAs($buyer)->post(route('pay'), ['ticket_id' => $ticket->id, 'phone_number' => '0712 345 678']);

        return [$buyer, $ticket->refresh(), $ticket->orders()->latest('id')->first()];
    }

    private function stkCallback(string $checkoutId = 'ws_CO_123', int $code = 0, int $amount = 2500, string $receipt = 'TKX123ABC'): array
    {
        $callback = [
            'MerchantRequestID' => 'M-1',
            'CheckoutRequestID' => $checkoutId,
            'ResultCode' => $code,
            'ResultDesc' => $code === 0 ? 'The service request is processed successfully.' : 'Request cancelled by user',
        ];

        if ($code === 0) {
            $callback['CallbackMetadata'] = ['Item' => [
                ['Name' => 'Amount', 'Value' => $amount],
                ['Name' => 'MpesaReceiptNumber', 'Value' => $receipt],
                ['Name' => 'TransactionDate', 'Value' => 20261104120000],
                ['Name' => 'PhoneNumber', 'Value' => 254712345678],
            ]];
        }

        return ['Body' => ['stkCallback' => $callback]];
    }

    // ---- Starting a checkout --------------------------------------------

    public function test_checkout_reserves_the_ticket_and_sends_a_correct_stk_push(): void
    {
        $this->fakeDaraja();
        [$buyer, $ticket, $order] = $this->startCheckout();

        $this->assertSame(Ticket::STATUS_RESERVED, $ticket->status);
        $this->assertTrue($ticket->reserved_until->isFuture());
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertEquals(2500, $order->amount, 'Rounded up to whole shillings');
        $this->assertEquals(250, $order->commission);
        $this->assertEquals(2250, $order->seller_amount);
        $this->assertSame('ws_CO_123', $order->payment->checkout_request_id);

        Http::assertSent(function (Request $request) use ($order) {
            if (! str_ends_with($request->url(), '/stkpush/v1/processrequest')) {
                return false;
            }
            $decoded = base64_decode($request['Password']);

            return $request->hasHeader('Authorization', 'Bearer tok')
                && $request['Amount'] === 2500
                && $request['PhoneNumber'] === '254712345678'
                && $request['PartyA'] === '254712345678'
                && $request['PartyB'] === '174379'
                && $request['AccountReference'] === 'TIX'.$order->id
                && $request['CallBackURL'] === 'https://tixswap.test/mpesa/callback/'.self::SECRET
                && $decoded === '174379passkey'.$request['Timestamp'];
        });
    }

    public function test_waiting_page_never_asks_for_the_mpesa_pin(): void
    {
        $this->fakeDaraja();
        [$buyer, $ticket, $order] = $this->startCheckout();

        $this->actingAs($buyer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Check your phone')
            ->assertSee('0712 *** 678')
            ->assertDontSee('type="password"', false);
    }

    public function test_double_submit_reuses_the_same_order_and_sends_one_prompt(): void
    {
        $this->fakeDaraja();
        [$buyer, $ticket, $order] = $this->startCheckout();

        $this->actingAs($buyer)->post(route('pay'), ['ticket_id' => $ticket->id, 'phone_number' => '0712345678'])
            ->assertRedirect(route('orders.show', $order));

        $this->assertSame(1, Order::count());
        Http::assertSentCount(2); // one OAuth token + one STK push
    }

    public function test_a_reserved_ticket_cannot_be_bought_by_someone_else(): void
    {
        $this->fakeDaraja();
        [, $ticket] = $this->startCheckout();
        $other = User::factory()->create();

        $this->actingAs($other)->get(route('tickets.show', $ticket))->assertOk()->assertSee('Someone is paying for this ticket');
        $this->actingAs($other)->post(route('pay'), ['ticket_id' => $ticket->id, 'phone_number' => '0722000000'])
            ->assertRedirect(route('dashboard'));

        $this->assertSame(1, Order::count());
    }

    public function test_reserved_tickets_cannot_be_edited_or_deleted(): void
    {
        $this->fakeDaraja();
        [, $ticket] = $this->startCheckout();

        $this->actingAs($ticket->seller)->get(route('seller.tickets.edit', $ticket))->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->delete(route('admin.listings.destroy', $ticket))->assertForbidden();
        $this->assertModelExists($ticket);
    }

    public function test_if_mpesa_is_unreachable_the_ticket_goes_back_on_sale(): void
    {
        $this->fakeDaraja(pushResponse: 500);
        $buyer = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAs($buyer)->from(route('tickets.show', $ticket))
            ->post(route('pay'), ['ticket_id' => $ticket->id, 'phone_number' => '0712345678'])
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHas('error');

        $this->assertSame(Ticket::STATUS_APPROVED, $ticket->fresh()->status);
        $this->assertSame(Order::STATUS_FAILED, $ticket->orders()->first()->status);
    }

    // ---- Safaricom callback ---------------------------------------------

    public function test_successful_callback_sells_the_ticket_and_holds_money_in_escrow(): void
    {
        $this->fakeDaraja();
        [$buyer, $ticket, $order] = $this->startCheckout();

        $this->postJson('/mpesa/callback/'.self::SECRET, $this->stkCallback())
            ->assertOk()
            ->assertExactJson(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);

        $order->refresh();
        $ticket->refresh();
        $this->assertSame(Order::STATUS_PAID, $order->status);
        $this->assertSame(Order::ESCROW_HELD, $order->escrow_status);
        $this->assertTrue($order->escrow_release_at->equalTo(\Carbon\Carbon::parse($ticket->event->date)->addHours(24)));
        $this->assertSame('TKX123ABC', $order->payment->transaction_id);
        $this->assertSame(Ticket::STATUS_SOLD, $ticket->status);
        $this->assertSame($buyer->id, $ticket->buyer_id);
        $this->assertNull($ticket->reserved_until);

        $this->actingAs($buyer)->getJson(route('orders.status', $order))->assertJson(['status' => 'paid']);
    }

    public function test_duplicate_callbacks_are_processed_once(): void
    {
        $this->fakeDaraja();
        [, $ticket] = $this->startCheckout();

        $this->postJson('/mpesa/callback/'.self::SECRET, $this->stkCallback())->assertOk();
        $code = $ticket->fresh()->validation_code;
        $this->postJson('/mpesa/callback/'.self::SECRET, $this->stkCallback())->assertOk();

        $this->assertSame($code, $ticket->fresh()->validation_code);
        $this->assertSame(1, Payment::where('status', Payment::STATUS_PAID)->count());
    }

    public function test_cancelled_payment_puts_the_ticket_back_on_sale(): void
    {
        $this->fakeDaraja();
        [$buyer, $ticket, $order] = $this->startCheckout();

        $this->postJson('/mpesa/callback/'.self::SECRET, $this->stkCallback(code: 1032))->assertOk();

        $this->assertSame(Order::STATUS_FAILED, $order->fresh()->status);
        $this->assertSame(Payment::STATUS_FAILED, $order->payment->fresh()->status);
        $this->assertSame(Ticket::STATUS_APPROVED, $ticket->fresh()->status);
        $this->assertNull($ticket->fresh()->buyer_id);

        $this->actingAs($buyer)->getJson(route('orders.status', $order))
            ->assertJson(['status' => 'failed'])
            ->assertJsonPath('message', 'The payment was not completed: Request cancelled by user');
    }

    public function test_callback_with_wrong_secret_is_rejected(): void
    {
        $this->fakeDaraja();
        [, $ticket, $order] = $this->startCheckout();

        $this->postJson('/mpesa/callback/wrong-secret', $this->stkCallback())->assertNotFound();

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
        $this->assertNull($ticket->fresh()->buyer_id);
    }

    public function test_callback_is_rejected_when_no_secret_is_configured(): void
    {
        config(['services.mpesa.callback_secret' => '']);

        $this->postJson('/mpesa/callback/anything', $this->stkCallback())->assertNotFound();
    }

    public function test_callback_for_unknown_checkout_is_acknowledged_and_ignored(): void
    {
        $this->postJson('/mpesa/callback/'.self::SECRET, $this->stkCallback('ws_CO_UNKNOWN'))->assertOk();
        $this->assertSame(0, Order::count());
    }

    public function test_underpayment_is_flagged_for_refund_not_delivered(): void
    {
        $this->fakeDaraja();
        [, $ticket, $order] = $this->startCheckout();

        $this->postJson('/mpesa/callback/'.self::SECRET, $this->stkCallback(amount: 1))->assertOk();

        $this->assertSame(Order::STATUS_NEEDS_REFUND, $order->fresh()->status);
        $this->assertNull($ticket->fresh()->buyer_id);
    }

    // ---- Status polling & STK query fallback ---------------------------

    public function test_status_check_asks_safaricom_when_the_callback_is_missing(): void
    {
        $this->fakeDaraja(['ResultCode' => '0', 'ResultDesc' => 'The service request is processed successfully.'], 200);
        [$buyer, $ticket, $order] = $this->startCheckout();

        $this->actingAs($buyer)->getJson(route('orders.status', $order))
            ->assertJson(['status' => 'paid', 'redirect' => route('payment.success', $ticket)]);

        $this->assertSame($buyer->id, $ticket->fresh()->buyer_id);
        $this->assertNull($order->payment->fresh()->transaction_id, 'Receipt arrives later with the callback');

        // The late callback then fills in the receipt without re-selling.
        $code = $ticket->fresh()->validation_code;
        $this->postJson('/mpesa/callback/'.self::SECRET, $this->stkCallback())->assertOk();
        $this->assertSame('TKX123ABC', $order->payment->fresh()->transaction_id);
        $this->assertSame($code, $ticket->fresh()->validation_code);
    }

    public function test_status_stays_pending_while_the_buyer_has_not_responded(): void
    {
        $this->fakeDaraja(); // query answers "still processing"
        [$buyer, , $order] = $this->startCheckout();

        $this->actingAs($buyer)->getJson(route('orders.status', $order))->assertJson(['status' => 'pending']);
    }

    public function test_only_the_buyer_can_see_an_order(): void
    {
        $this->fakeDaraja();
        [, , $order] = $this->startCheckout();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get(route('orders.show', $order))->assertForbidden();
        $this->actingAs($stranger)->getJson(route('orders.status', $order))->assertForbidden();
    }

    // ---- Expiry ----------------------------------------------------------

    public function test_unpaid_reservations_expire_and_the_ticket_is_released(): void
    {
        $this->fakeDaraja();
        [, $ticket, $order] = $this->startCheckout();

        $this->travel(13)->minutes();
        $this->artisan('tixswap:expire-reservations')->assertSuccessful();

        $this->assertSame(Order::STATUS_EXPIRED, $order->fresh()->status);
        $this->assertSame(Ticket::STATUS_APPROVED, $ticket->fresh()->status);
    }

    public function test_after_a_reservation_expires_another_buyer_can_purchase(): void
    {
        $this->fakeDaraja();
        [, $ticket, $firstOrder] = $this->startCheckout();
        $this->travel(11)->minutes();

        [$second, , $secondOrder] = $this->startCheckout(ticket: $ticket);

        $this->assertNotSame($firstOrder->id, $secondOrder->id);
        $this->assertSame(Order::STATUS_EXPIRED, $firstOrder->fresh()->status);
        $this->assertSame(Order::STATUS_PENDING, $secondOrder->status);
        $this->assertSame('ws_CO_456', $secondOrder->payment->checkout_request_id);
    }

    public function test_late_payment_after_ticket_was_sold_to_someone_else_is_flagged_for_refund(): void
    {
        $this->fakeDaraja();
        [$first, $ticket, $firstOrder] = $this->startCheckout();
        $this->travel(11)->minutes();

        [$second] = $this->startCheckout(ticket: $ticket);
        $this->postJson('/mpesa/callback/'.self::SECRET, $this->stkCallback('ws_CO_456', receipt: 'SECOND'))->assertOk();

        // The first buyer's payment finally goes through.
        $this->postJson('/mpesa/callback/'.self::SECRET, $this->stkCallback('ws_CO_123', receipt: 'FIRST'))->assertOk();

        $this->assertSame($second->id, $ticket->fresh()->buyer_id);
        $this->assertSame(Order::STATUS_NEEDS_REFUND, $firstOrder->fresh()->status);
        $this->assertSame('FIRST', $firstOrder->payment->fresh()->transaction_id);
    }

    public function test_late_payment_is_honoured_if_the_ticket_is_still_free(): void
    {
        $this->fakeDaraja();
        [$buyer, $ticket, $order] = $this->startCheckout();
        $this->travel(13)->minutes();
        $this->artisan('tixswap:expire-reservations');
        $this->assertSame(Order::STATUS_EXPIRED, $order->fresh()->status);

        $this->postJson('/mpesa/callback/'.self::SECRET, $this->stkCallback())->assertOk();

        $this->assertSame(Order::STATUS_PAID, $order->fresh()->status);
        $this->assertSame($buyer->id, $ticket->fresh()->buyer_id);
    }

    // ---- Escrow & payouts ---------------------------------------------------

    public function test_escrow_is_released_into_a_seller_payout_after_the_event(): void
    {
        $order = Order::factory()->paidInEscrow(now()->addDays(2))->create();

        $this->artisan('tixswap:release-escrow')->assertSuccessful();
        $this->assertSame(0, Payout::count(), 'Not before the release time');

        $this->travel(3)->days();
        $this->artisan('tixswap:release-escrow')->assertSuccessful();
        $this->artisan('tixswap:release-escrow')->assertSuccessful(); // safe to run twice

        $payout = Payout::sole();
        $this->assertSame($order->ticket->user_id, $payout->seller_id);
        $this->assertEquals(2250, $payout->amount);
        $this->assertSame(Order::ESCROW_RELEASED, $order->fresh()->escrow_status);
    }

    public function test_admin_records_a_payout_and_the_seller_sees_it(): void
    {
        $order = Order::factory()->paidInEscrow(now()->subHour())->create();
        $this->artisan('tixswap:release-escrow');
        $payout = Payout::sole();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.payouts.index'))->assertOk()->assertSee('KES 2,250');
        $this->actingAs($admin)->patch(route('admin.payouts.paid', $payout), ['reference' => 'tkb12xyz'])->assertRedirect();

        $this->assertSame(Payout::STATUS_PAID, $payout->fresh()->status);
        $this->assertSame('TKB12XYZ', $payout->fresh()->reference);

        $this->actingAs($order->ticket->seller)->get(route('seller.dashboard'))
            ->assertOk()->assertSeeInOrder(['Paid out', 'KES 2,250']);
    }

    public function test_admin_records_a_refund(): void
    {
        $order = Order::factory()->create(['status' => Order::STATUS_NEEDS_REFUND]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.payouts.index'))->assertOk()->assertSee('Refunds needed');
        $this->actingAs($admin)->patch(route('admin.orders.refunded', $order), ['reference' => 'RFD123'])->assertRedirect();

        $this->assertSame(Order::STATUS_REFUNDED, $order->fresh()->status);
    }

    public function test_non_admins_cannot_touch_payouts(): void
    {
        Order::factory()->paidInEscrow(now()->subHour())->create();
        $this->artisan('tixswap:release-escrow');
        $payout = Payout::sole();

        $this->actingAs($payout->seller)->get(route('admin.payouts.index'))->assertForbidden();
        $this->actingAs($payout->seller)->patch(route('admin.payouts.paid', $payout), ['reference' => 'X'])->assertForbidden();
        $this->assertSame(Payout::STATUS_PENDING, $payout->fresh()->status);
    }

    public function test_sold_tickets_cannot_be_deleted_even_by_admins(): void
    {
        $order = Order::factory()->paidInEscrow()->create();
        $order->ticket->forceFill(['status' => Ticket::STATUS_SOLD, 'buyer_id' => $order->user_id])->save();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.listings.destroy', $order->ticket))->assertForbidden();
        $this->assertModelExists($order);
    }

    // ---- Seller payout number --------------------------------------------

    public function test_seller_can_save_a_payout_number(): void
    {
        $seller = User::factory()->seller()->create();

        $this->actingAs($seller)->patch(route('profile.update'), [
            'name' => $seller->name, 'email' => $seller->email, 'payout_phone' => '0798 765 432',
        ])->assertSessionHasNoErrors();

        $this->assertSame('254798765432', $seller->fresh()->payout_phone);
    }

    public function test_buyers_cannot_set_a_payout_number(): void
    {
        $buyer = User::factory()->create();

        $this->actingAs($buyer)->patch(route('profile.update'), [
            'name' => $buyer->name, 'email' => $buyer->email, 'payout_phone' => '0798765432',
        ]);

        $this->assertNull($buyer->fresh()->payout_phone);
    }

    // ---- Configuration -----------------------------------------------------

    public function test_fake_driver_is_refused_in_production(): void
    {
        config(['services.mpesa.driver' => 'fake']);
        $this->app['env'] = 'production';

        $this->expectException(\RuntimeException::class);
        $this->app->make(MpesaGateway::class);
    }

    public function test_phone_numbers_are_normalised(): void
    {
        foreach (['0712345678', '712345678', '254712345678', '+254712345678', '0712 345 678', '0110123456'] as $input) {
            $this->assertMatchesRegularExpression('/^254[17]\d{8}$/', PhoneNumber::toMsisdn($input), $input);
        }
        foreach (['0812345678', '12345', 'abc', '+1 555 123 4567'] as $input) {
            $this->assertNull(PhoneNumber::toMsisdn($input), $input);
        }
        $this->assertSame('0712 *** 678', PhoneNumber::mask('254712345678'));
    }
}
