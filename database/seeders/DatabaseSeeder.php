<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Import the Hash facade

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test Admin user with a known password
        User::create([
            'name' => 'Joy',
            'email' => 'joy@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create a test Seller user with a known password
        User::create([
            'name' => 'Tom',
            'email' => 'tom@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'email_verified_at' => now(),
        ]);

        // You can add other seeders here later, for example:
        // $this->call(EventSeeder::class);
    }
}