<?php

declare(strict_types=1);

namespace Tests\Feature\PaymentRequest;

use App\Models\PaymentRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class RejectPaymentRequestTest extends TestCase
{
    use InteractsWithRoles;
    use RefreshDatabase;

    public function test_finance_can_reject_a_pending_request(): void
    {
        $employee = $this->createEmployee();
        $finance = $this->createFinance();
        $payment = PaymentRequest::factory()->pending()->create(['user_id' => $employee->id]);

        Sanctum::actingAs($finance);

        $this->patchJson("/api/payment-requests/{$payment->id}/reject")
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.reviewer.id', $finance->id);

        $this->assertDatabaseHas('payment_requests', [
            'id' => $payment->id,
            'status' => 'rejected',
            'reviewed_by' => $finance->id,
        ]);
    }

    public function test_employee_cannot_reject_a_request(): void
    {
        $employee = $this->createEmployee();
        $payment = PaymentRequest::factory()->pending()->create(['user_id' => $employee->id]);

        Sanctum::actingAs($employee);

        $this->patchJson("/api/payment-requests/{$payment->id}/reject")
            ->assertForbidden();
    }
}
