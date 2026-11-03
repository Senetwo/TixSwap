<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payout;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\PayoutSent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class B2CPayoutTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'cb-secret';

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::factory()->admin()->create();
        config([
            'app.url' => 'https://tixswap.test',
            'services.mpesa' => [
                'driver' => 'daraja', 'environment' => 'sandbox',
                'consumer_key' => 'key', 'consumer_secret' => 'secret', 'shortcode' => '174379', 'passkey' => 'pk',
                'transaction_type' => 'CustomerPayBillOnline', 'party_b' => null, 'callback_base_url' => null,
                'callback_secret' => self::SECRET,
                'b2c_enabled' => true, 'b2c_auto' => false, 'b2c_shortcode' => '600000',
                'b2c_initiator_name' => 'tixapi', 'b2c_security_credential' => 'ENCRYPTED==',
            ],
        ]);
    }

    private function payout(?string $phone = '254798765432'): Payout
    {
        $seller = User::factory()->seller()->create(['payout_phone' => $phone]);
        Order::factory()->paidInEscrow(now()->subHour())->create(['ticket_id' => Ticket::factory()->create(['user_id' => $seller->id])->id]);
        $this->artisan('tixswap:release-escrow');

        return Payout::latest('id')->first();
    }

    private function fakeDaraja(int $status = 200): void
    {
        Http::fake([
            'sandbox.safaricom.co.ke/oauth/*' => Http::response(['access_token' => 'tok', 'expires_in' => '3599']),
            'sandbox.safaricom.co.ke/mpesa/b2c/v3/paymentrequest' => $status === 200
                ? Http::response(['ConversationID' => 'AG_1', 'OriginatorConversationID' => 'x', 'ResponseCode' => '0', 'ResponseDescription' => 'Accept the service request successfully.'])
                : Http::response(['errorMessage' => 'Invalid Initiator'], $status),
        ]);
    }

    private function b2cResult(Payout $payout, int $code = 0): array
    {
        return ['Result' => [
            'ResultType' => 0, 'ResultCode' => $code,
            'ResultDesc' => $code === 0 ? 'The service request is processed successfully.' : 'The balance is insufficient for the transaction.',
            'OriginatorConversationID' => $payout->fresh()->originator_conversation_id,
            'ConversationID' => 'AG_1', 'TransactionID' => 'TKR123',
            'ResultParameters' => ['ResultParameter' => [
                ['Key' => 'TransactionAmount', 'Value' => 2250],
                ['Key' => 'TransactionReceipt', 'Value' => 'TKR123'],
            ]],
        ]];
    }

    public function test_sending_a_payout_calls_daraja_b2c_correctly(): void
    {
        $this->fakeDaraja();
        $payout = $this->payout();

        $this->actingAs($this->admin)->post(route('admin.payouts.send', $payout))->assertSessionHas('success');

        $payout->refresh();
        $this->assertSame(Payout::STATUS_PROCESSING, $payout->status);
        $this->assertSame('mpesa_b2c', $payout->method);
        $this->assertSame('AG_1', $payout->conversation_id);

        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/mpesa/b2c/v3/paymentrequest')
            && $r['Amount'] === 2250
            && $r['PartyA'] === '600000'
            && $r['PartyB'] === '254798765432'
            && $r['CommandID'] === 'BusinessPayment'
            && $r['SecurityCredential'] === 'ENCRYPTED=='
            && $r['OriginatorConversationID'] === $payout->originator_conversation_id
            && $r['ResultURL'] === 'https://tixswap.test/mpesa/callback/'.self::SECRET.'/b2c-result'
            && $r['QueueTimeOutURL'] === 'https://tixswap.test/mpesa/callback/'.self::SECRET.'/b2c-timeout');
    }

    public function test_successful_result_marks_paid_and_notifies_once(): void
    {
        Notification::fake();
        $this->fakeDaraja();
        $payout = $this->payout();
        $this->actingAs($this->admin)->post(route('admin.payouts.send', $payout));

        $this->postJson('/mpesa/callback/'.self::SECRET.'/b2c-result', $this->b2cResult($payout))->assertOk();
        $this->postJson('/mpesa/callback/'.self::SECRET.'/b2c-result', $this->b2cResult($payout))->assertOk(); // duplicate

        $payout->refresh();
        $this->assertSame(Payout::STATUS_PAID, $payout->status);
        $this->assertSame('TKR123', $payout->reference);
        Notification::assertSentToTimes($payout->seller, PayoutSent::class, 1);
    }

    public function test_a_payout_cannot_be_sent_twice(): void
    {
        $this->fakeDaraja();
        $payout = $this->payout();

        $this->actingAs($this->admin)->post(route('admin.payouts.send', $payout))->assertSessionHas('success');
        $this->actingAs($this->admin)->post(route('admin.payouts.send', $payout))->assertSessionHas('error', 'This payout has already been sent.');

        Http::assertSentCount(2); // token + one payment request
    }

    public function test_failed_result_allows_a_retry(): void
    {
        $this->fakeDaraja();
        $payout = $this->payout();
        $this->actingAs($this->admin)->post(route('admin.payouts.send', $payout));
        $firstId = $payout->fresh()->originator_conversation_id;

        $this->postJson('/mpesa/callback/'.self::SECRET.'/b2c-result', $this->b2cResult($payout, 2001))->assertOk();
        $this->assertSame(Payout::STATUS_FAILED, $payout->fresh()->status);
        $this->assertStringContainsString('insufficient', $payout->fresh()->failure_reason);

        $this->actingAs($this->admin)->post(route('admin.payouts.send', $payout))->assertSessionHas('success');
        $this->assertNotSame($firstId, $payout->fresh()->originator_conversation_id, 'each attempt gets a new id');
        $this->assertSame(2, $payout->fresh()->attempts);
    }

    public function test_a_timeout_does_not_allow_a_resend_until_an_admin_confirms(): void
    {
        $this->fakeDaraja();
        $payout = $this->payout();
        $this->actingAs($this->admin)->post(route('admin.payouts.send', $payout));

        $this->postJson('/mpesa/callback/'.self::SECRET.'/b2c-timeout', ['Result' => ['OriginatorConversationID' => $payout->fresh()->originator_conversation_id]])->assertOk();
        $this->assertSame(Payout::STATUS_PROCESSING, $payout->fresh()->status);
        $this->actingAs($this->admin)->post(route('admin.payouts.send', $payout))->assertSessionHas('error');

        $this->actingAs($this->admin)->patch(route('admin.payouts.failed', $payout))->assertSessionHas('success');
        $this->actingAs($this->admin)->post(route('admin.payouts.send', $payout))->assertSessionHas('success');
    }

    public function test_request_rejected_by_daraja_fails_cleanly(): void
    {
        $this->fakeDaraja(400);
        $payout = $this->payout();

        $this->actingAs($this->admin)->post(route('admin.payouts.send', $payout))->assertSessionHas('error');
        $this->assertSame(Payout::STATUS_FAILED, $payout->fresh()->status);
    }

    public function test_payouts_need_a_phone_number_and_the_feature_switched_on(): void
    {
        $this->fakeDaraja();
        $noPhone = $this->payout(null);
        $this->actingAs($this->admin)->post(route('admin.payouts.send', $noPhone))->assertSessionHas('error');

        config(['services.mpesa.b2c_enabled' => false]);
        $this->actingAs($this->admin)->post(route('admin.payouts.send', $noPhone))->assertSessionHas('error');
        Http::assertNothingSent();
    }

    public function test_b2c_callback_rejects_wrong_secret(): void
    {
        $payout = $this->payout();
        $this->postJson('/mpesa/callback/wrong/b2c-result', $this->b2cResult($payout))->assertNotFound();
    }

    public function test_send_all_and_fake_driver_settle_instantly(): void
    {
        config(['services.mpesa.driver' => 'fake']);
        $a = $this->payout();
        $b = $this->payout();

        $this->actingAs($this->admin)->post(route('admin.payouts.send-all'))->assertSessionHas('success', 'Sent 2 payouts.');
        $this->assertSame([Payout::STATUS_PAID, Payout::STATUS_PAID], [$a->fresh()->status, $b->fresh()->status]);
        $this->assertStringStartsWith('FAKE', $a->fresh()->reference);
    }

    public function test_send_payouts_command_for_automatic_mode(): void
    {
        config(['services.mpesa.driver' => 'fake']);
        $payout = $this->payout();

        $this->artisan('tixswap:send-payouts')->expectsOutputToContain('Sent 1 payout(s)')->assertSuccessful();
        $this->assertSame(Payout::STATUS_PAID, $payout->fresh()->status);
    }
}
