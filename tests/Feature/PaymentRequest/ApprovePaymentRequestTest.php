<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentRequest;

use App\Models\PaymentRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class ApprovePaymentRequestTest extends TestCase
{
    use InteractsWithRoles;
    use RefreshDatabase;

    public function test_finance_can_approve_a_pending_request(): void
    {
        $employee = $this->createEmployee();
        $finance = $this->createFinance();
        $payment = PaymentRequest::factory()->pending()->create(['user_id' => $employee->id]);

        Sanctum::actingAs($finance);

        $this->patchJson("/api/payment-requests/{$payment->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.reviewer.id', $finance->id);

        $this->assertDatabaseHas('payment_requests', [
            'id' => $payment->id,
            'status' => 'approved',
            'reviewed_by' => $finance->id,
        ]);
    }

    public function test_employee_cannot_approve_a_request(): void
    {
        $employee = $this->createEmployee();
        $payment = PaymentRequest::factory()->pending()->create(['user_id' => $employee->id]);

        Sanctum::actingAs($employee);

        $this->patchJson("/api/payment-requests/{$payment->id}/approve")
            ->assertForbidden();
    }

    public function test_an_already_reviewed_request_cannot_be_approved_again(): void
    {
        $finance = $this->createFinance();
        $payment = PaymentRequest::factory()->approved()->create([
            'user_id' => $this->createEmployee()->id,
        ]);

        Sanctum::actingAs($finance);

        $this->patchJson("/api/payment-requests/{$payment->id}/approve")
            ->assertForbidden();
    }
}
