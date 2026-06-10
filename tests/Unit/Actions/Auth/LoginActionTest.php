<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\LoginAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoginActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_user_and_a_token_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'ana@example.com',
            'password' => Hash::make('password123'),
        ]);

        $result = (new LoginAction())->handle([
            'email' => 'ana@example.com',
            'password' => 'password123',
        ]);

        $this->assertTrue($user->is($result['user']));
        $this->assertNotEmpty($result['token']);
        $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $user->id]);
    }

    public function test_it_throws_for_an_unknown_email(): void
    {
        $this->expectException(ValidationException::class);

        (new LoginAction())->handle([
            'email' => 'ghost@example.com',
            'password' => 'password123',
        ]);
    }

    public function test_it_throws_for_a_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'ana@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->expectException(ValidationException::class);

        (new LoginAction())->handle([
            'email' => 'ana@example.com',
            'password' => 'wrong-password',
        ]);
    }
}
