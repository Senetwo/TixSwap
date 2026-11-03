<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsoleCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_make_admin_promotes_an_existing_account(): void
    {
        $user = User::factory()->create(['email' => 'owner@tixswap.test']);

        $this->artisan('tixswap:make-admin', ['email' => 'owner@tixswap.test'])->assertSuccessful();
        $this->assertTrue($user->fresh()->isAdmin());

        $this->artisan('tixswap:make-admin', ['email' => 'nobody@tixswap.test'])->assertFailed();
    }
}
