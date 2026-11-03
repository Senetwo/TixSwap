<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory()->seller(),
            'price' => 2500,
            'face_value' => 2500,
            'seat_details' => 'Section A, Row 5',
            'original_vendor' => 'Ticketsasa',
            'purchase_proof' => 'purchase_proofs/example.jpg',
            'status' => Ticket::STATUS_APPROVED,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => Ticket::STATUS_PENDING]);
    }

    public function soldTo(User $buyer): static
    {
        return $this->state(fn () => [
            'status' => Ticket::STATUS_SOLD,
            'buyer_id' => $buyer->id,
            'validation_code' => (string) Str::uuid(),
        ]);
    }
}
