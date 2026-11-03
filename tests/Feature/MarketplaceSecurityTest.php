<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ---- Role middleware -------------------------------------------------

    public function test_buyers_and_sellers_cannot_reach_admin_pages(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->seller()->create();

        $this->actingAs($buyer)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($seller)->get(route('admin.tickets.pending'))->assertForbidden();
        $this->actingAs($buyer)->delete(route('admin.users.destroy', $seller))->assertForbidden();
        $this->assertModelExists($seller);
    }

    public function test_buyers_cannot_reach_seller_pages(): void
    {
        $buyer = User::factory()->create();

        $this->actingAs($buyer)->get(route('seller.dashboard'))->assertForbidden();
        $this->actingAs($buyer)->get(route('seller.tickets.create'))->assertForbidden();
    }

    public function test_buyers_cannot_approve_tickets(): void
    {
        $buyer = User::factory()->create();
        $ticket = Ticket::factory()->pending()->create();

        $this->actingAs($buyer)->patch(route('admin.tickets.approve', $ticket))->assertForbidden();
        $this->assertSame(Ticket::STATUS_PENDING, $ticket->fresh()->status);
    }

    public function test_admin_can_approve_a_pending_ticket(): void
    {
        $admin = User::factory()->admin()->create();
        $ticket = Ticket::factory()->pending()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->patch(route('admin.tickets.approve', $ticket))->assertRedirect();
        $this->assertSame(Ticket::STATUS_APPROVED, $ticket->fresh()->status);
    }

    public function test_admin_can_delete_a_user_but_not_themselves(): void
    {
        $admin = User::factory()->admin()->create();
        $buyer = User::factory()->create();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $buyer))->assertRedirect();
        $this->assertModelMissing($buyer);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertRedirect();
        $this->assertModelExists($admin);
    }

    // ---- Purchasing ------------------------------------------------------

    public function test_buyer_can_purchase_an_approved_ticket(): void
    {
        // Default test driver is the fake M-Pesa gateway: payment succeeds
        // on the first status check.
        $buyer = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $response = $this->actingAs($buyer)
            ->post(route('pay'), ['ticket_id' => $ticket->id, 'phone_number' => '0712345678']);

        $order = $ticket->orders()->firstOrFail();
        $response->assertRedirect(route('orders.show', $order));

        $this->actingAs($buyer)->getJson(route('orders.status', $order))
            ->assertOk()
            ->assertJson(['status' => 'paid', 'redirect' => route('payment.success', $ticket)]);

        $ticket->refresh();
        $this->assertSame(Ticket::STATUS_SOLD, $ticket->status);
        $this->assertSame($buyer->id, $ticket->buyer_id);
        $this->assertNotNull($ticket->validation_code);

        $this->actingAs($buyer)->get(route('payment.success', $ticket))->assertOk();
        $this->actingAs($buyer)->get(route('tickets.purchased.show', $ticket))->assertOk();
    }

    public function test_pending_tickets_cannot_be_purchased(): void
    {
        $buyer = User::factory()->create();
        $ticket = Ticket::factory()->pending()->create();

        $this->actingAs($buyer)
            ->post(route('pay'), ['ticket_id' => $ticket->id, 'phone_number' => '0712345678'])
            ->assertRedirect(route('home'));

        $this->assertNull($ticket->fresh()->buyer_id);
    }

    public function test_a_sold_ticket_cannot_be_bought_again(): void
    {
        $firstBuyer = User::factory()->create();
        $secondBuyer = User::factory()->create();
        $ticket = Ticket::factory()->soldTo($firstBuyer)->create();
        $originalCode = $ticket->validation_code;

        $this->actingAs($secondBuyer)
            ->post(route('pay'), ['ticket_id' => $ticket->id, 'phone_number' => '0712345678'])
            ->assertRedirect(route('home'));

        $ticket->refresh();
        $this->assertSame($firstBuyer->id, $ticket->buyer_id);
        $this->assertSame($originalCode, $ticket->validation_code);
    }

    public function test_sellers_cannot_buy_their_own_ticket(): void
    {
        $ticket = Ticket::factory()->create();

        $this->actingAs($ticket->seller)
            ->post(route('pay'), ['ticket_id' => $ticket->id, 'phone_number' => '0712345678'])
            ->assertRedirect(route('home'));

        $this->assertNull($ticket->fresh()->buyer_id);
    }

    public function test_invalid_phone_numbers_are_rejected(): void
    {
        $buyer = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAs($buyer)
            ->post(route('pay'), ['ticket_id' => $ticket->id, 'phone_number' => 'abc123456'])
            ->assertSessionHasErrors('phone_number');
    }

    public function test_other_users_cannot_see_someone_elses_purchased_ticket(): void
    {
        $buyer = User::factory()->create();
        $stranger = User::factory()->create();
        $ticket = Ticket::factory()->soldTo($buyer)->create();

        $this->actingAs($stranger)->get(route('payment.success', $ticket))->assertForbidden();
        $this->actingAs($stranger)->get(route('tickets.purchased.show', $ticket))->assertForbidden();
    }

    public function test_sold_tickets_are_not_shown_on_the_purchase_page(): void
    {
        $buyer = User::factory()->create();
        $ticket = Ticket::factory()->soldTo($buyer)->create();

        $this->actingAs(User::factory()->create())->get(route('tickets.show', $ticket))->assertNotFound();
    }

    // ---- Seller listing management --------------------------------------

    public function test_seller_can_edit_their_listing_and_it_returns_to_review(): void
    {
        $ticket = Ticket::factory()->create();

        $this->actingAs($ticket->seller)->get(route('seller.tickets.edit', $ticket))->assertOk();

        $this->actingAs($ticket->seller)
            ->patch(route('seller.tickets.update', $ticket), ['price' => 3000, 'seat_details' => 'VIP'])
            ->assertRedirect(route('seller.dashboard'));

        $ticket->refresh();
        $this->assertEquals(3000, $ticket->price);
        $this->assertSame(Ticket::STATUS_PENDING, $ticket->status);
    }

    public function test_seller_cannot_edit_another_sellers_listing(): void
    {
        $ticket = Ticket::factory()->create();
        $otherSeller = User::factory()->seller()->create();

        $this->actingAs($otherSeller)->get(route('seller.tickets.edit', $ticket))->assertForbidden();
        $this->actingAs($otherSeller)
            ->patch(route('seller.tickets.update', $ticket), ['price' => 1, 'seat_details' => 'x'])
            ->assertForbidden();
        $this->actingAs($otherSeller)->delete(route('seller.tickets.destroy', $ticket))->assertForbidden();

        $this->assertEquals(2500, $ticket->fresh()->price);
    }

    public function test_seller_can_delete_an_unsold_listing_but_not_a_sold_one(): void
    {
        $unsold = Ticket::factory()->create();
        $sold = Ticket::factory()->soldTo(User::factory()->create())->create(['user_id' => $unsold->user_id]);

        $this->actingAs($unsold->seller)->delete(route('seller.tickets.destroy', $unsold))->assertRedirect();
        $this->assertModelMissing($unsold);

        $this->actingAs($unsold->seller)->delete(route('seller.tickets.destroy', $sold))->assertForbidden();
        $this->assertModelExists($sold);
    }

    public function test_new_listings_store_proof_privately_and_start_pending(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $seller = User::factory()->seller()->create();

        $this->actingAs($seller)->post(route('seller.tickets.store'), [
            'event_name' => 'Blankets & Wine',
            'location' => 'Nairobi',
            'date' => now()->addWeek()->toDateTimeString(),
            'price' => 4000,
            'seat_details' => 'General Admission',
            'event_image' => UploadedFile::fake()->image('event.jpg'),
            'purchase_proof' => UploadedFile::fake()->image('receipt.jpg'),
        ])->assertRedirect(route('seller.dashboard'));

        $ticket = Ticket::firstOrFail();
        $this->assertSame(Ticket::STATUS_PENDING, $ticket->status);
        Storage::disk('local')->assertExists($ticket->purchase_proof);
        Storage::disk('public')->assertMissing($ticket->purchase_proof);
    }

    // ---- Proof of purchase access ---------------------------------------

    public function test_only_the_seller_and_admins_can_view_the_proof(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('purchase_proofs/example.jpg', 'fake-image');
        $ticket = Ticket::factory()->create();

        $this->actingAs(User::factory()->create())->get(route('tickets.proof', $ticket))->assertForbidden();
        $this->actingAs($ticket->seller)->get(route('tickets.proof', $ticket))->assertOk();
        $this->actingAs(User::factory()->admin()->create())->get(route('tickets.proof', $ticket))->assertOk();
    }

    // ---- Mass assignment -------------------------------------------------

    public function test_status_and_buyer_cannot_be_mass_assigned(): void
    {
        $ticket = Ticket::factory()->pending()->create();
        $ticket->fill(['status' => Ticket::STATUS_SOLD, 'buyer_id' => 999]);

        $this->assertSame(Ticket::STATUS_PENDING, $ticket->status);
        $this->assertNull($ticket->buyer_id);
    }
}
