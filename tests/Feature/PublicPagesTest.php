<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guests_can_browse_upcoming_events_with_ticket_counts(): void
    {
        $event = Event::factory()->create(['name' => 'Sol Fest']);
        Ticket::factory()->count(2)->create(['event_id' => $event->id, 'price' => 3000]);
        Ticket::factory()->create(['event_id' => $event->id, 'price' => 2500]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Sol Fest')
            ->assertSee('3 tickets')
            ->assertSee('KES 2,500');
    }

    public function test_events_without_tickets_on_sale_or_in_the_past_are_hidden(): void
    {
        $soldOut = Event::factory()->create(['name' => 'Sold Out Show']);
        Ticket::factory()->pending()->create(['event_id' => $soldOut->id]);
        $past = Event::factory()->create(['name' => 'Last Year Gig', 'date' => now()->subDay()]);
        Ticket::factory()->create(['event_id' => $past->id]);

        $this->get(route('home'))->assertOk()->assertDontSee('Sold Out Show')->assertDontSee('Last Year Gig');
    }

    public function test_search_filters_by_name_or_city(): void
    {
        Ticket::factory()->create(['event_id' => Event::factory()->create(['name' => 'Koroga', 'location' => 'Nairobi'])->id]);
        Ticket::factory()->create(['event_id' => Event::factory()->create(['name' => 'Beach Party', 'location' => 'Diani'])->id]);

        $this->get(route('home', ['search' => 'diani']))->assertSee('Beach Party')->assertDontSee('Koroga');
    }

    public function test_buyers_dashboard_redirects_to_the_marketplace(): void
    {
        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertRedirect(route('home'));
        $this->actingAs(User::factory()->seller()->create())->get(route('dashboard'))->assertRedirect(route('seller.dashboard'));
    }

    public function test_event_page_lists_available_tickets_cheapest_first(): void
    {
        $event = Event::factory()->create(['name' => 'Afro Nation']);
        Ticket::factory()->create(['event_id' => $event->id, 'price' => 9000, 'seat_details' => 'VIP']);
        Ticket::factory()->create(['event_id' => $event->id, 'price' => 4000, 'seat_details' => 'Regular']);
        Ticket::factory()->soldTo(User::factory()->create())->create(['event_id' => $event->id, 'seat_details' => 'Already Sold']);

        $this->get(route('events.show', [$event, $event->slug()]))
            ->assertOk()
            ->assertSeeInOrder(['Regular', 'VIP'])
            ->assertDontSee('Already Sold')
            ->assertSee('"@type":"Event"', false);
    }

    public function test_guests_can_view_a_ticket_and_are_sent_back_to_it_after_login(): void
    {
        $ticket = Ticket::factory()->create();

        $this->get(route('tickets.show', $ticket))->assertOk()->assertSee('Log in to buy');

        $this->get(route('tickets.buy', $ticket))->assertRedirect(route('login'));
        $buyer = User::factory()->create();
        $this->post(route('login'), ['email' => $buyer->email, 'password' => 'password'])
            ->assertRedirect(route('tickets.buy', $ticket));
    }

    public function test_sitemap_lists_events_on_sale(): void
    {
        $event = Event::factory()->create(['name' => 'Jazz Night']);
        Ticket::factory()->create(['event_id' => $event->id]);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('events.show', [$event, $event->slug()]), false);
    }

    public function test_public_policy_pages_work_for_guests(): void
    {
        foreach (['about', 'support', 'terms', 'privacy'] as $page) {
            $this->get(route($page))->assertOk();
        }
    }

    // ---- Event grouping when listing ----------------------------------------

    private function list(User $seller, array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($seller)->post(route('seller.tickets.store'), $overrides + [
            'event_name' => 'Blankets & Wine',
            'location' => 'Carnivore, Nairobi',
            'date' => now()->addWeek()->setTime(14, 0)->toDateTimeString(),
            'price' => 3000,
            'face_value' => 3000,
            'seat_details' => 'GA',
            'event_image' => UploadedFile::fake()->image('e.jpg'),
            'purchase_proof' => UploadedFile::fake()->createWithContent('p.png', uniqid()),
        ]);
    }

    public function test_listing_the_same_event_reuses_it(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $this->list(User::factory()->seller()->create());
        // Different spelling/case, same day, later time.
        $this->list(User::factory()->seller()->create(), [
            'event_name' => 'blankets and wine',
            'date' => now()->addWeek()->setTime(18, 0)->toDateTimeString(),
        ]);
        $this->list(User::factory()->seller()->create(), ['event_name' => 'Blankets & Wine!']);

        $this->assertSame(1, Event::count());
        $this->assertSame(3, Event::sole()->tickets()->count());
    }

    public function test_seller_can_join_an_existing_event_without_a_cover_image(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $event = Event::factory()->create(['name' => 'Sol Fest', 'date' => now()->addMonth()]);
        $seller = User::factory()->seller()->create();

        $this->actingAs($seller)->getJson(route('seller.events.search', ['q' => 'sol']))
            ->assertOk()->assertJsonPath('0.id', $event->id);

        $this->list($seller, ['event_id' => $event->id, 'event_name' => 'Sol Fest', 'event_image' => null])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('seller.dashboard'));

        $this->assertSame(1, Event::count());
        $this->assertSame($event->id, Ticket::sole()->event_id);
    }

    public function test_event_search_is_for_sellers_only(): void
    {
        $this->actingAs(User::factory()->create())->getJson(route('seller.events.search', ['q' => 'sol']))->assertForbidden();
    }
}
