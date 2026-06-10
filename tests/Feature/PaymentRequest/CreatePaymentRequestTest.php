<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentRequest;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreatePaymentRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_an_authenticated_user_can_create_a_payment_request(): void
    {
        Http::fake([
            '*' => Http::response(['base' => 'EUR', 'rates' => ['BRL' => 5.0]]),
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/payment-requests', [
            'amount' => 500,
            'currency' => 'BRL',
            'description' => 'Office supplies',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.currency', 'BRL')
            ->assertJsonPath('data.amount_in_eur', '100.00')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('payment_requests', [
            'currency' => 'BRL',
            'amount_in_eur' => 100.00,
            'status' => 'pending',
        ]);
    }

    public function test_it_validates_required_fields(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/payment-requests', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount', 'currency']);
    }

    public function test_it_rejects_an_unsupported_currency(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/payment-requests', [
            'amount' => 100,
            'currency' => 'XYZ',
        ])->assertUnprocessable()->assertJsonValidationErrors('currency');
    }

    public function test_guests_cannot_create_payment_requests(): void
    {
        $this->postJson('/api/payment-requests', [
            'amount' => 100,
            'currency' => 'BRL',
        ])->assertUnauthorized();
    }
}
