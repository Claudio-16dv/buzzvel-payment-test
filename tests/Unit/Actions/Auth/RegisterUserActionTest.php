<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\RegisterUserAction;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterUserActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_it_creates_a_user_with_hashed_password(): void
    {
        $user = (new RegisterUserAction())->handle([
            'name' => 'Ana Costa',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'country' => 'Brazil',
            'currency' => 'brl',
        ]);

        $this->assertSame('ana@example.com', $user->email);
        $this->assertSame('BRL', $user->currency);
        $this->assertNotSame('password123', $user->password);
        $this->assertTrue(password_verify('password123', $user->password));
    }

    public function test_it_assigns_the_employee_role_by_default(): void
    {
        $user = (new RegisterUserAction())->handle([
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'country' => 'Brazil',
            'currency' => 'BRL',
        ]);

        $this->assertTrue($user->hasRole('employee'));
        $this->assertFalse($user->hasRole('finance'));
    }

    public function test_it_assigns_the_given_role(): void
    {
        $user = (new RegisterUserAction())->handle([
            'name' => 'Sofia',
            'email' => 'sofia@example.com',
            'password' => 'password123',
            'country' => 'Portugal',
            'currency' => 'EUR',
            'role' => 'finance',
        ]);

        $this->assertTrue($user->hasRole('finance'));
    }
}
