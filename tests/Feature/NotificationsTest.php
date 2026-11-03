<?php

namespace Tests\Feature;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\DisputeOpened;
use App\Notifications\DisputeResolved;
use App\Notifications\ListingApproved;
use App\Notifications\ListingRejected;
use App\Notifications\PayoutSent;
use App\Notifications\PurchaseConfirmed;
use App\Notifications\RefundSent;
use App\Notifications\TicketSold;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_seller_is_told_when_a_listing_is_approved_or_rejected(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $approve = Ticket::factory()->pending()->create();
        $reject = Ticket::factory()->pending()->create();

        $this->actingAs($admin)->patch(route('admin.tickets.approve', $approve));
        $this->actingAs($admin)->patch(route('admin.tickets.reject', $reject), ['rejection_reason' => 'Blurry receipt']);

        Notification::assertSentTo($approve->seller, ListingApproved::class);
        Notification::assertSentTo($reject->seller, ListingRejected::class, fn ($n) => str_contains($n->body(), 'Blurry receipt'));
    }

    public function test_buyer_and_seller_are_told_about_a_sale(): void
    {
        Notification::fake();
        $buyer = User::factory()->create();
        $ticket = Ticket::factory()->create();

        // Fake M-Pesa driver: confirmed on the first status check.
        $this->actingAs($buyer)->post(route('pay'), ['ticket_id' => $ticket->id, 'phone_number' => '0712345678']);
        $this->actingAs($buyer)->getJson(route('orders.status', $ticket->orders()->first()));

        Notification::assertSentTo($buyer, PurchaseConfirmed::class);
        Notification::assertSentTo($ticket->seller, TicketSold::class, fn ($n) => str_contains($n->body(), 'KES 2,250'));
    }

    public function test_disputes_notify_the_seller_admins_and_both_sides_on_resolution(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->paidInEscrow()->create();
        $order->ticket->forceFill(['status' => 'sold', 'buyer_id' => $order->user_id, 'validation_code' => (string) Str::uuid()])->save();

        $this->actingAs($order->user)->post(route('disputes.store', $order), [
            'reason' => 'already_used', 'details' => 'The scanner said it was already used at the gate.',
        ]);
        Notification::assertSentTo($order->ticket->seller, DisputeOpened::class, fn ($n) => ! $n->forAdmin);
        Notification::assertSentTo($admin, DisputeOpened::class, fn ($n) => $n->forAdmin);

        $this->actingAs($admin)->patch(route('admin.disputes.resolve', Dispute::sole()), ['decision' => 'refund', 'note' => 'Confirmed invalid.']);
        Notification::assertSentTo($order->user, DisputeResolved::class, fn ($n) => str_contains($n->body(), 'in your favour'));
        Notification::assertSentTo($order->ticket->seller, DisputeResolved::class, fn ($n) => $n->forSeller);
    }

    public function test_payouts_and_refunds_notify_the_recipient(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        Order::factory()->paidInEscrow(now()->subHour())->create();
        $this->artisan('tixswap:release-escrow');
        $payout = Payout::sole();
        $refund = Order::factory()->create(['status' => Order::STATUS_NEEDS_REFUND]);

        $this->actingAs($admin)->patch(route('admin.payouts.paid', $payout), ['reference' => 'TKB1']);
        $this->actingAs($admin)->patch(route('admin.orders.refunded', $refund), ['reference' => 'RFD9']);

        Notification::assertSentTo($payout->seller, PayoutSent::class, fn ($n) => str_contains($n->sms(), 'TKB1'));
        Notification::assertSentTo($refund->buyer, RefundSent::class, fn ($n) => $n->reference === 'RFD9');
    }

    public function test_notifications_appear_in_the_bell_and_open_marks_them_read(): void
    {
        $seller = User::factory()->seller()->create();
        $ticket = Ticket::factory()->create(['user_id' => $seller->id]);
        $seller->notify(new ListingApproved($ticket));

        $this->actingAs($seller)->get(route('seller.dashboard'))->assertSee('Your ticket is live')->assertSee('1 unread');

        $id = $seller->notifications()->first()->id;
        $this->actingAs($seller)->post(route('notifications.open', $id))->assertRedirect(route('tickets.show', $ticket));
        $this->assertNotNull($seller->notifications()->first()->read_at);
    }

    public function test_users_cannot_open_someone_elses_notification(): void
    {
        $seller = User::factory()->seller()->create();
        $seller->notify(new ListingApproved(Ticket::factory()->create(['user_id' => $seller->id])));

        $this->actingAs(User::factory()->create())
            ->post(route('notifications.open', $seller->notifications()->first()->id))
            ->assertNotFound();
    }

    public function test_mark_all_as_read(): void
    {
        $seller = User::factory()->seller()->create();
        $ticket = Ticket::factory()->create(['user_id' => $seller->id]);
        $seller->notify(new ListingApproved($ticket));
        $seller->notify(new ListingApproved($ticket));

        $this->actingAs($seller)->post(route('notifications.read-all'))->assertRedirect();
        $this->assertSame(0, $seller->unreadNotifications()->count());
    }

    public function test_sms_is_sent_through_africas_talking_when_configured(): void
    {
        config(['services.sms' => ['driver' => 'africastalking', 'username' => 'tixswap', 'api_key' => 'key', 'sender_id' => null]]);
        Http::fake(['api.africastalking.com/*' => Http::response(['SMSMessageData' => ['Recipients' => []]], 201)]);
        $seller = User::factory()->seller()->create(['payout_phone' => '254798765432']);
        Order::factory()->paidInEscrow(now()->subHour())->create(['ticket_id' => Ticket::factory()->create(['user_id' => $seller->id])->id]);
        $this->artisan('tixswap:release-escrow');
        $payout = Payout::sole();
        $payout->forceFill(['status' => 'paid', 'reference' => 'TKB1'])->save();

        (new SmsChannel)->send($seller, new PayoutSent($payout));

        Http::assertSent(fn ($r) => $r->url() === 'https://api.africastalking.com/version1/messaging'
            && $r->hasHeader('apiKey', 'key')
            && $r['to'] === '+254798765432'
            && str_contains($r['message'], 'TKB1'));
    }

    public function test_a_failing_mail_server_never_breaks_a_payment(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1]);
        $buyer = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAs($buyer)->post(route('pay'), ['ticket_id' => $ticket->id, 'phone_number' => '0712345678']);
        $this->actingAs($buyer)->getJson(route('orders.status', $ticket->orders()->first()))->assertJson(['status' => 'paid']);

        $this->assertSame($buyer->id, $ticket->fresh()->buyer_id);
        // The in-app notification was still stored (database channel runs first).
        $this->assertSame(1, $buyer->notifications()->count());
    }
}
