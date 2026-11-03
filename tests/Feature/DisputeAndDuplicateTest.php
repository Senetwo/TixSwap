<?php

namespace Tests\Feature;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DisputeAndDuplicateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** A paid order whose money is in escrow, with the ticket sold to the buyer. */
    private function paidOrder(?\DateTimeInterface $releaseAt = null): Order
    {
        $order = Order::factory()->paidInEscrow($releaseAt ?? now()->addDays(3))->create();
        $order->ticket->forceFill([
            'status' => Ticket::STATUS_SOLD,
            'buyer_id' => $order->user_id,
            'validation_code' => (string) \Illuminate\Support\Str::uuid(),
        ])->save();

        return $order;
    }

    private function openDispute(Order $order, array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($order->user)->post(route('disputes.store', $order), $overrides + [
            'reason' => 'rejected_at_entry',
            'details' => 'The scanner at gate B said this ticket had already been used.',
        ]);
    }

    // ---- Disputes: buyer side ----------------------------------------------

    public function test_buyer_sees_report_link_on_their_ticket(): void
    {
        $order = $this->paidOrder();

        $this->actingAs($order->user)->get(route('tickets.purchased.show', $order->ticket))
            ->assertOk()
            ->assertSee(route('disputes.create', $order));
    }

    public function test_buyer_can_open_a_dispute_which_freezes_escrow(): void
    {
        Storage::fake('local');
        $order = $this->paidOrder();

        $this->openDispute($order, ['evidence' => UploadedFile::fake()->image('scanner.jpg')])
            ->assertRedirect(route('tickets.purchased.show', $order->ticket_id))
            ->assertSessionHas('success');

        $dispute = Dispute::sole();
        $this->assertSame(Dispute::STATUS_OPEN, $dispute->status);
        Storage::disk('local')->assertExists($dispute->evidence_path);
        $this->assertSame(Order::ESCROW_DISPUTED, $order->fresh()->escrow_status);

        // Escrow release time passes, but the money stays frozen.
        $this->travel(5)->days();
        $this->artisan('tixswap:release-escrow');
        $this->assertSame(0, Payout::count());

        $this->actingAs($order->user)->get(route('tickets.purchased.show', $order->ticket))->assertSee('Problem reported');
    }

    public function test_a_dispute_needs_a_reason_and_real_details(): void
    {
        $order = $this->paidOrder();

        $this->openDispute($order, ['reason' => 'made_up', 'details' => 'bad'])
            ->assertSessionHasErrors(['reason', 'details']);
        $this->assertSame(0, Dispute::count());
    }

    public function test_only_the_buyer_can_dispute(): void
    {
        $order = $this->paidOrder();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get(route('disputes.create', $order))->assertForbidden();
        $this->actingAs($stranger)->post(route('disputes.store', $order), [
            'reason' => 'other', 'details' => 'Trying to block someone else\'s payout here.',
        ])->assertForbidden();
        $this->actingAs($order->ticket->seller)->post(route('disputes.store', $order), [
            'reason' => 'other', 'details' => 'Seller trying to dispute their own sale.',
        ])->assertForbidden();

        $this->assertSame(0, Dispute::count());
    }

    public function test_cannot_dispute_twice_or_after_escrow_is_released(): void
    {
        $order = $this->paidOrder();
        $this->openDispute($order);
        $this->openDispute($order)->assertSessionHas('error');
        $this->assertSame(1, Dispute::count());

        $released = $this->paidOrder(now()->subHour());
        $this->artisan('tixswap:release-escrow');
        $this->actingAs($released->user)->get(route('disputes.create', $released))->assertRedirect();
        $this->openDispute($released)->assertSessionHas('error');
        $this->assertSame(1, Dispute::count());
    }

    public function test_unpaid_orders_cannot_be_disputed(): void
    {
        $order = Order::factory()->create(); // pending

        $this->openDispute($order)->assertSessionHas('error');
        $this->assertSame(0, Dispute::count());
    }

    // ---- Disputes: admin side ----------------------------------------------

    public function test_admin_refund_decision_sends_order_to_the_refund_list(): void
    {
        $order = $this->paidOrder();
        $this->openDispute($order);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.disputes.index'))->assertOk()->assertSee('already been used');
        $this->actingAs($admin)->patch(route('admin.disputes.resolve', Dispute::sole()), [
            'decision' => 'refund', 'note' => 'Organiser confirmed the ticket was cancelled.',
        ])->assertRedirect();

        $order->refresh();
        $this->assertSame(Order::STATUS_NEEDS_REFUND, $order->status);
        $this->assertSame(Order::ESCROW_REFUNDED, $order->escrow_status);
        $this->assertSame(Dispute::STATUS_REFUNDED, Dispute::sole()->status);

        $this->actingAs($admin)->get(route('admin.payouts.index'))->assertSee('Refunds needed');
        $this->actingAs($order->user)->get(route('tickets.purchased.show', $order->ticket))
            ->assertSee('Resolved in your favour')
            ->assertSee('Organiser confirmed the ticket was cancelled.');

        // A refunded order never pays the seller.
        $this->travel(5)->days();
        $this->artisan('tixswap:release-escrow');
        $this->assertSame(0, Payout::count());
    }

    public function test_admin_release_decision_pays_the_seller_immediately(): void
    {
        $order = $this->paidOrder();
        $this->openDispute($order);

        $this->actingAs(User::factory()->admin()->create())->patch(route('admin.disputes.resolve', Dispute::sole()), [
            'decision' => 'release', 'note' => 'Scan logs show the buyer entered the venue.',
        ])->assertRedirect();

        $this->assertSame(Order::ESCROW_RELEASED, $order->fresh()->escrow_status);
        $this->assertEquals(2250, Payout::sole()->amount);
        $this->assertSame(Dispute::STATUS_RELEASED, Dispute::sole()->status);
    }

    public function test_a_dispute_cannot_be_resolved_twice(): void
    {
        $order = $this->paidOrder();
        $this->openDispute($order);
        $admin = User::factory()->admin()->create();
        $dispute = Dispute::sole();

        $this->actingAs($admin)->patch(route('admin.disputes.resolve', $dispute), ['decision' => 'release', 'note' => 'ok']);
        $this->actingAs($admin)->patch(route('admin.disputes.resolve', $dispute), ['decision' => 'refund', 'note' => 'changed mind'])
            ->assertSessionHas('error');

        $this->assertSame(Dispute::STATUS_RELEASED, $dispute->fresh()->status);
        $this->assertSame(1, Payout::count());
    }

    public function test_non_admins_cannot_see_or_resolve_disputes(): void
    {
        $order = $this->paidOrder();
        $this->openDispute($order);
        $seller = $order->ticket->seller;

        $this->actingAs($seller)->get(route('admin.disputes.index'))->assertForbidden();
        $this->actingAs($seller)->patch(route('admin.disputes.resolve', Dispute::sole()), ['decision' => 'release', 'note' => 'mine'])
            ->assertForbidden();
        $this->actingAs($seller)->get(route('admin.disputes.evidence', Dispute::sole()))->assertForbidden();
        $this->assertTrue(Dispute::sole()->isOpen());
    }

    public function test_seller_sees_that_a_sale_is_on_hold(): void
    {
        $order = $this->paidOrder();
        $this->openDispute($order);

        $this->actingAs($order->ticket->seller)->get(route('seller.dashboard'))
            ->assertOk()
            ->assertSee('Buyer reported a problem')
            ->assertSee('KES 2,250 on hold');
    }

    // ---- Duplicate-ticket detection ----------------------------------------

    private function listTicket(User $seller, UploadedFile $proof, ?string $reference = null): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($seller)->post(route('seller.tickets.store'), [
            'event_name' => 'Sol Fest',
            'location' => 'Nairobi',
            'date' => now()->addWeek()->toDateTimeString(),
            'price' => 3000,
            'face_value' => 3000,
            'seat_details' => 'VIP',
            'ticket_reference' => $reference,
            'event_image' => UploadedFile::fake()->image('event.jpg'),
            'purchase_proof' => $proof,
        ]);
    }

    public function test_listing_stores_proof_fingerprint_and_normalised_reference(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $proof = UploadedFile::fake()->createWithContent('receipt.png', 'receipt-bytes-1');

        $this->listTicket(User::factory()->seller()->create(), $proof, ' tkt-482 13ab ')->assertRedirect(route('seller.dashboard'));

        $ticket = Ticket::sole();
        $this->assertSame(hash('sha256', 'receipt-bytes-1'), $ticket->proof_hash);
        $this->assertSame('TKT48213AB', $ticket->ticket_reference);
    }

    public function test_the_same_proof_from_another_seller_is_blocked(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $this->listTicket(User::factory()->seller()->create(), UploadedFile::fake()->createWithContent('a.png', 'same-receipt'));

        $this->listTicket(User::factory()->seller()->create(), UploadedFile::fake()->createWithContent('b.png', 'same-receipt'))
            ->assertSessionHasErrors('purchase_proof');

        $this->assertSame(1, Ticket::count());
    }

    public function test_the_same_proof_from_the_same_seller_is_allowed_but_flagged(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $seller = User::factory()->seller()->create();
        $this->listTicket($seller, UploadedFile::fake()->createWithContent('a.png', 'two-ticket-receipt'));
        $this->listTicket($seller, UploadedFile::fake()->createWithContent('a.png', 'two-ticket-receipt'))->assertSessionHasNoErrors();

        $this->assertSame(2, Ticket::count());
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.tickets.pending'))
            ->assertOk()
            ->assertSee('Same proof file as listing #1')
            ->assertSee('same seller');
    }

    public function test_a_rejected_listings_proof_can_be_reused(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $this->listTicket(User::factory()->seller()->create(), UploadedFile::fake()->createWithContent('a.png', 'receipt'));
        Ticket::sole()->forceFill(['status' => Ticket::STATUS_REJECTED])->save();

        $this->listTicket(User::factory()->seller()->create(), UploadedFile::fake()->createWithContent('a.png', 'receipt'))
            ->assertSessionHasNoErrors();
        $this->assertSame(2, Ticket::count());
    }

    public function test_the_same_ticket_number_from_another_seller_is_flagged_for_admins(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $this->listTicket(User::factory()->seller()->create(['name' => 'First Seller']), UploadedFile::fake()->createWithContent('a.png', 'one'), 'ABC-123');
        $this->listTicket(User::factory()->seller()->create(), UploadedFile::fake()->createWithContent('b.png', 'two'), 'abc 123')
            ->assertSessionHasNoErrors();

        $this->actingAs(User::factory()->admin()->create())->get(route('admin.tickets.pending'))
            ->assertSee('Same ticket number as listing #1')
            ->assertSee('different seller: First Seller');
    }

    public function test_hash_proofs_command_backfills_existing_listings(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('purchase_proofs/old.jpg', 'old-receipt');
        $ticket = Ticket::factory()->create(['purchase_proof' => 'purchase_proofs/old.jpg']);

        $this->artisan('tixswap:hash-proofs')->assertSuccessful();

        $this->assertSame(hash('sha256', 'old-receipt'), $ticket->fresh()->proof_hash);
    }
}
