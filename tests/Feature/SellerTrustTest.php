<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Dispute;
use App\Models\Event;
use App\Models\Order;
use App\Models\SellerRating;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SellerTrustTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** A completed (escrow released) sale for the seller. */
    private function completedSale(User $seller, ?User $buyer = null): Order
    {
        $buyer ??= User::factory()->create();
        $event = Event::factory()->create(['date' => now()->subDays(3)]);
        $ticket = Ticket::factory()->soldTo($buyer)->create(['user_id' => $seller->id, 'event_id' => $event->id]);

        return Order::factory()->paidInEscrow(now()->subDay())->create([
            'user_id' => $buyer->id, 'ticket_id' => $ticket->id, 'escrow_status' => Order::ESCROW_RELEASED,
        ]);
    }

    public function test_new_sellers_payouts_are_held_longer(): void
    {
        $new = User::factory()->seller()->create();
        $trusted = User::factory()->seller()->create();
        foreach (range(1, 3) as $i) {
            $this->completedSale($trusted);
        }
        $this->assertTrue($new->isNewSeller());
        $this->assertTrue($trusted->isTrustedSeller());

        $buyer = User::factory()->create();
        $event = Event::factory()->create(['date' => now()->addWeek()->startOfHour()]);
        foreach ([$new, $trusted] as $seller) {
            $ticket = Ticket::factory()->create(['user_id' => $seller->id, 'event_id' => $event->id]);
            $this->actingAs($buyer)->post(route('pay'), ['ticket_id' => $ticket->id, 'phone_number' => '0712345678']);
            $this->actingAs($buyer)->getJson(route('orders.status', $ticket->orders()->first()));
        }

        $newOrder = Order::whereHas('ticket', fn ($q) => $q->where('user_id', $new->id))->whereNotNull('paid_at')->sole();
        $trustedOrder = Order::whereHas('ticket', fn ($q) => $q->where('user_id', $trusted->id))->whereNotNull('paid_at')->latest('id')->first();
        $this->assertTrue($trustedOrder->escrow_release_at->equalTo($event->date->copy()->addHours(24)));
        $this->assertTrue($newOrder->escrow_release_at->equalTo($event->date->copy()->addHours(72)));
    }

    public function test_an_upheld_dispute_removes_trusted_status(): void
    {
        $seller = User::factory()->seller()->create();
        foreach (range(1, 3) as $i) {
            $order = $this->completedSale($seller);
        }
        $dispute = new Dispute;
        $dispute->forceFill(['order_id' => $order->id, 'opened_by' => $order->user_id, 'reason' => 'other', 'details' => 'x', 'status' => Dispute::STATUS_REFUNDED])->save();

        $this->assertFalse($seller->fresh()->isTrustedSeller());
    }

    public function test_buyers_see_trusted_badge_and_rating_on_ticket_page(): void
    {
        $seller = User::factory()->seller()->create();
        foreach (range(1, 3) as $i) {
            $order = $this->completedSale($seller);
            $r = new SellerRating;
            $r->forceFill(['order_id' => $order->id, 'buyer_id' => $order->user_id, 'seller_id' => $seller->id, 'positive' => $i !== 3])->save();
        }
        $listing = Ticket::factory()->create(['user_id' => $seller->id]);

        // 2 of 3 positive = 67%: not trusted (needs 90%), but the rating is shown.
        $this->get(route('tickets.show', $listing))->assertSee('67% positive · 3 ratings')->assertDontSee('Trusted seller');
    }

    public function test_buyer_can_rate_the_seller_once_after_the_event(): void
    {
        $seller = User::factory()->seller()->create();
        $buyer = User::factory()->create();
        $order = $this->completedSale($seller, $buyer);
        $order->forceFill(['status' => Order::STATUS_PAID])->save();
        $order->ticket->forceFill(['validation_code' => (string) Str::uuid()])->save();

        $this->actingAs($buyer)->get(route('tickets.purchased.show', $order->ticket))->assertSee('How did it go with');
        $this->actingAs($buyer)->post(route('ratings.store', $order), ['positive' => 1, 'comment' => 'Smooth entry'])->assertSessionHas('success');
        $this->actingAs($buyer)->post(route('ratings.store', $order), ['positive' => 0])->assertForbidden();

        $this->assertSame(1, SellerRating::count());
        $this->assertSame(100, $seller->fresh()->sellerStats()['percent']);
    }

    public function test_cannot_rate_before_the_event_or_someone_elses_purchase(): void
    {
        $buyer = User::factory()->create();
        $order = Order::factory()->paidInEscrow()->create(['user_id' => $buyer->id]); // event is in the future

        $this->actingAs($buyer)->post(route('ratings.store', $order), ['positive' => 1])->assertForbidden();
        $this->actingAs(User::factory()->create())->post(route('ratings.store', $order), ['positive' => 1])->assertForbidden();
        $this->assertSame(0, SellerRating::count());
    }

    public function test_admin_actions_are_recorded_in_the_activity_log(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Ada Admin']);
        $approve = Ticket::factory()->pending()->create();
        $reject = Ticket::factory()->pending()->create();
        $buyer = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.tickets.approve', $approve));
        $this->actingAs($admin)->patch(route('admin.tickets.reject', $reject), ['rejection_reason' => 'Fake']);
        $this->actingAs($admin)->delete(route('admin.users.destroy', $buyer));

        $this->assertEqualsCanonicalizing(['listing.approved', 'listing.rejected', 'user.deleted'], AuditLog::pluck('action')->all());
        $this->assertSame($admin->id, AuditLog::where('action', 'listing.rejected')->value('user_id'));

        $this->actingAs($admin)->get(route('admin.activity.index'))
            ->assertOk()->assertSee('Ada Admin')->assertSee('rejected a listing')->assertSee('Fake');
        $this->actingAs($admin)->get(route('admin.activity.index', ['action' => 'user.deleted']))
            ->assertSee('deleted a user')->assertDontSee('rejected a listing');
    }

    public function test_only_admins_see_the_activity_log(): void
    {
        $this->actingAs(User::factory()->seller()->create())->get(route('admin.activity.index'))->assertForbidden();
    }
}
