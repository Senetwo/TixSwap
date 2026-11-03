<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScannerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function soldTicket(array $attrs = []): Ticket
    {
        return Ticket::factory()->soldTo(User::factory()->create(['name' => 'Brenda A']))->create($attrs);
    }

    public function test_gate_staff_and_admins_can_open_the_scanner_others_cannot(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'scanner']))->get(route('scan.index'))->assertOk()->assertSee('Gate scanner');
        $this->actingAs(User::factory()->admin()->create())->get(route('scan.index'))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('scan.index'))->assertForbidden();
        $this->actingAs(User::factory()->seller()->create())->postJson(route('scan.check'), ['code' => 'x'])->assertForbidden();
    }

    public function test_a_valid_ticket_is_admitted_once(): void
    {
        $staff = User::factory()->create(['role' => 'scanner', 'name' => 'Gate One']);
        $ticket = $this->soldTicket();

        $this->actingAs($staff)->postJson(route('scan.check'), ['code' => strtoupper($ticket->validation_code)])
            ->assertJson(['result' => 'admitted', 'ticket' => ['holder' => 'Brenda A']]);
        $this->assertNotNull($ticket->fresh()->checked_in_at);
        $this->assertSame($staff->id, $ticket->fresh()->checked_in_by);

        $this->actingAs($staff)->postJson(route('scan.check'), ['code' => $ticket->validation_code])
            ->assertJson(['result' => 'already_used'])
            ->assertJsonPath('message', fn ($m) => str_contains($m, 'by Gate One'));
    }

    public function test_unknown_or_unsold_codes_are_invalid(): void
    {
        $staff = User::factory()->create(['role' => 'scanner']);
        $unsold = Ticket::factory()->create(['validation_code' => '11111111-2222-3333-4444-555555555555']);

        $this->actingAs($staff)->postJson(route('scan.check'), ['code' => 'not-a-ticket'])->assertJson(['result' => 'invalid']);
        $this->actingAs($staff)->postJson(route('scan.check'), ['code' => $unsold->validation_code])->assertJson(['result' => 'invalid']);
    }

    public function test_tickets_for_another_event_are_flagged(): void
    {
        $staff = User::factory()->create(['role' => 'scanner']);
        $ticket = $this->soldTicket();
        $otherEvent = Event::factory()->create();

        $this->actingAs($staff)->postJson(route('scan.check'), ['code' => $ticket->validation_code, 'event_id' => $otherEvent->id])
            ->assertJson(['result' => 'wrong_event']);
        $this->assertNull($ticket->fresh()->checked_in_at);
    }

    public function test_refunded_tickets_are_blocked(): void
    {
        $staff = User::factory()->create(['role' => 'scanner']);
        $ticket = $this->soldTicket();
        Order::factory()->create(['ticket_id' => $ticket->id, 'user_id' => $ticket->buyer_id, 'status' => Order::STATUS_REFUNDED]);

        $this->actingAs($staff)->postJson(route('scan.check'), ['code' => $ticket->validation_code])->assertJson(['result' => 'blocked']);
        $this->assertNull($ticket->fresh()->checked_in_at);
    }

    public function test_buyer_sees_when_their_ticket_was_scanned(): void
    {
        $ticket = $this->soldTicket();
        $ticket->forceFill(['checked_in_at' => now()])->save();

        $this->actingAs($ticket->buyer)->get(route('tickets.purchased.show', $ticket))->assertSee('Scanned at the entrance');
        $this->actingAs($ticket->buyer)->get(route('cart.index'))->assertSee('Used ·');
    }

    public function test_admin_can_make_someone_gate_staff_but_not_an_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.users.role', $user), ['role' => 'scanner'])->assertSessionHas('success');
        $this->assertTrue($user->fresh()->isScanner());
        $this->assertSame('user.role_changed', AuditLog::sole()->action);

        $this->actingAs($admin)->patch(route('admin.users.role', $user), ['role' => 'admin'])->assertSessionHasErrors('role');
        $this->assertFalse($user->fresh()->isAdmin());

        $this->actingAs($user->fresh())->get(route('dashboard'))->assertRedirect(route('scan.index'));
    }
}
