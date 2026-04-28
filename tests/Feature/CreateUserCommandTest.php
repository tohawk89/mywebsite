<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_create_command_creates_admin_user(): void
    {
        $this->artisan('user:create', [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Password123!',
        ])->assertExitCode(0);

        $user = User::where('email', 'admin@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->is_admin);
        $this->assertNotNull($user->email_verified_at);
    }
}
