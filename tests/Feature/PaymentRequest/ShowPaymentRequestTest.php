<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentRequest;

use App\Models\PaymentRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class ShowPaymentRequestTest extends TestCase
{
    use InteractsWithRoles;
    use RefreshDatabase;

    public function test_the_owner_can_view_their_request(): void
    {
        $employee = $this->createEmployee();
        $payment = PaymentRequest::factory()->create(['user_id' => $employee->id]);

        Sanctum::actingAs($employee);

        $this->getJson("/api/payment-requests/{$payment->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $payment->id);
    }

    public function test_finance_can_view_any_request(): void
    {
        $employee = $this->createEmployee();
        $finance = $this->createFinance();
        $payment = PaymentRequest::factory()->create(['user_id' => $employee->id]);

        Sanctum::actingAs($finance);

        $this->getJson("/api/payment-requests/{$payment->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $payment->id);
    }

    public function test_an_employee_cannot_view_someone_elses_request(): void
    {
        $owner = $this->createEmployee();
        $intruder = $this->createEmployee();
        $payment = PaymentRequest::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($intruder);

        $this->getJson("/api/payment-requests/{$payment->id}")
            ->assertForbidden();
    }
}
