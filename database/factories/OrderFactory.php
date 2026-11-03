<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ticket_id' => Ticket::factory(),
            'amount' => 2500,
            'commission' => 250,
            'seller_amount' => 2250,
            'phone_number' => '254712345678',
            'status' => Order::STATUS_PENDING,
            'expires_at' => now()->addMinutes(10),
        ];
    }

    public function paidInEscrow(?\DateTimeInterface $releaseAt = null): static
    {
        return $this->state(fn () => [
            'status' => Order::STATUS_PAID,
            'paid_at' => now(),
            'escrow_status' => Order::ESCROW_HELD,
            'escrow_release_at' => $releaseAt ?? now()->addDay(),
        ]);
    }
}
