<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PriceCapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
        Storage::fake('public');
        config(['services.tixswap.max_markup_percent' => 20]);
    }

    private function list(array $overrides): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs(User::factory()->seller()->create())->post(route('seller.tickets.store'), $overrides + [
            'event_name' => 'Sol Fest',
            'location' => 'Nairobi',
            'date' => now()->addWeek()->toDateTimeString(),
            'seat_details' => 'VIP',
            'event_image' => UploadedFile::fake()->image('e.jpg'),
            'purchase_proof' => UploadedFile::fake()->createWithContent('p.png', uniqid()),
        ]);
    }

    public function test_price_up_to_the_cap_is_accepted(): void
    {
        $this->list(['face_value' => 2500, 'price' => 3000])->assertSessionHasNoErrors();
        $this->assertEquals(2500, Ticket::sole()->face_value);
    }

    public function test_price_above_the_cap_is_rejected_with_the_maximum(): void
    {
        $this->list(['face_value' => 2500, 'price' => 3001])
            ->assertSessionHasErrors(['price' => 'The most you can charge is KES 3,000 (20% above face value).']);
        $this->assertSame(0, Ticket::count());
    }

    public function test_face_value_is_required(): void
    {
        $this->list(['price' => 3000])->assertSessionHasErrors('face_value');
    }

    public function test_no_cap_when_disabled(): void
    {
        config(['services.tixswap.max_markup_percent' => null]);
        $this->list(['face_value' => 1000, 'price' => 9000])->assertSessionHasNoErrors();
    }

    public function test_editing_cannot_exceed_the_cap(): void
    {
        $ticket = Ticket::factory()->create(['face_value' => 2000, 'price' => 2000]);

        $this->actingAs($ticket->seller)
            ->patch(route('seller.tickets.update', $ticket), ['price' => 5000, 'seat_details' => 'VIP'])
            ->assertSessionHasErrors('price');
        $this->assertEquals(2000, $ticket->fresh()->price);
    }

    public function test_buyers_see_the_face_value_and_markup(): void
    {
        $ticket = Ticket::factory()->create(['face_value' => 2000, 'price' => 2300]);

        $this->get(route('tickets.show', $ticket))->assertSee('Face value KES 2,000')->assertSee('15% above face value');

        $atFace = Ticket::factory()->create(['face_value' => 2000, 'price' => 2000]);
        $this->get(route('tickets.show', $atFace))->assertSee('At face value');
    }
}
