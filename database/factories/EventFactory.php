<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true).' Live',
            'location' => fake()->city(),
            'date' => now()->addMonth(),
            'image_url' => 'event_images/example.jpg',
            'original_vendor' => 'Ticketsasa',
        ];
    }
}
