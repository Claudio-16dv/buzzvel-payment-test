<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentRequest;

use App\Models\PaymentRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class ListPaymentRequestTest extends TestCase
{
    use InteractsWithRoles;
    use RefreshDatabase;

    public function test_an_employee_only_sees_their_own_requests(): void
    {
        $employee = $this->createEmployee();
        $other = $this->createEmployee();

        PaymentRequest::factory()->count(2)->create(['user_id' => $employee->id]);
        PaymentRequest::factory()->count(3)->create(['user_id' => $other->id]);

        Sanctum::actingAs($employee);

        $this->getJson('/api/payment-requests')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_finance_sees_every_request(): void
    {
        $employee = $this->createEmployee();
        $finance = $this->createFinance();

        PaymentRequest::factory()->count(4)->create(['user_id' => $employee->id]);

        Sanctum::actingAs($finance);

        $this->getJson('/api/payment-requests')
            ->assertOk()
            ->assertJsonCount(4, 'data');
    }

    public function test_it_filters_by_status(): void
    {
        $employee = $this->createEmployee();

        PaymentRequest::factory()->pending()->count(2)->create(['user_id' => $employee->id]);
        PaymentRequest::factory()->approved()->count(3)->create(['user_id' => $employee->id]);

        Sanctum::actingAs($employee);

        $this->getJson('/api/payment-requests?status=approved')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_it_rejects_an_invalid_status_filter(): void
    {
        Sanctum::actingAs($this->createEmployee());

        $this->getJson('/api/payment-requests?status=banana')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }
}
