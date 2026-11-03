<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_sales_conversion_and_top_events(): void
    {
        $this->withoutVite();
        $admin = User::factory()->admin()->create();
        $paid = Order::factory()->count(3)->paidInEscrow()->create(['amount' => 2000, 'commission' => 200]);
        Order::factory()->create(['status' => Order::STATUS_FAILED]);
        Order::factory()->create(['status' => Order::STATUS_REFUNDED, 'paid_at' => now(), 'amount' => 9999]);

        $ticket = Ticket::factory()->pending()->create(['created_at' => now()->subHours(5)]);
        $this->actingAs($admin);
        AuditLog::record('listing.approved', $ticket);

        $this->get(route('admin.analytics.index'))
            ->assertOk()
            ->assertSee('KES 6,000')               // refunded order excluded
            ->assertSee('KES 600')                 // revenue
            ->assertSee('60%')                     // 3 of 5 checkouts paid
            ->assertSee('3 of 5 checkouts paid')
            ->assertSee('5 h')                     // median review time
            ->assertSee($paid->first()->ticket->event->name)
            ->assertSee('Show as table');
    }

    public function test_range_filter_and_access(): void
    {
        $this->withoutVite();
        Order::factory()->paidInEscrow()->create(['amount' => 5000, 'paid_at' => now()->subDays(60)]);

        $admin = User::factory()->admin()->create();
        // The Sales tile follows the range (the weekly chart always shows 12 weeks).
        $this->actingAs($admin)->get(route('admin.analytics.index', ['days' => 30]))->assertSee('0 paid orders');
        $this->actingAs($admin)->get(route('admin.analytics.index', ['days' => 90]))->assertSee('1 paid order ·');

        $this->actingAs(User::factory()->seller()->create())->get(route('admin.analytics.index'))->assertForbidden();
    }
}
