<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_a_user_can_register_and_receive_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ana Costa',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country' => 'Brazil',
            'currency' => 'BRL',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'email', 'roles'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'ana@example.com', 'currency' => 'BRL']);
    }

    public function test_it_assigns_the_employee_role_by_default(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Ana Costa',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country' => 'Brazil',
            'currency' => 'BRL',
        ])->assertCreated()
            ->assertJsonPath('user.roles', ['employee']);
    }

    public function test_it_rejects_an_invalid_currency(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Ana Costa',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country' => 'Brazil',
            'currency' => 'XYZ',
        ])->assertUnprocessable()->assertJsonValidationErrors('currency');
    }

    public function test_it_rejects_a_duplicate_email(): void
    {
        Role::query()->firstWhere('name', 'employee');

        $this->postJson('/api/register', [
            'name' => 'First',
            'email' => 'dup@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country' => 'Brazil',
            'currency' => 'BRL',
        ])->assertCreated();

        $this->postJson('/api/register', [
            'name' => 'Second',
            'email' => 'dup@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country' => 'Portugal',
            'currency' => 'EUR',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }
}
