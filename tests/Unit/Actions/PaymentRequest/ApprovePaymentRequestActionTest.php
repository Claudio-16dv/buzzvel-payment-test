<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\PaymentRequest;

use App\Actions\PaymentRequest\ApprovePaymentRequestAction;
use App\Enums\PaymentStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovePaymentRequestActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_the_request_as_approved_and_records_the_reviewer(): void
    {
        $employee = User::factory()->create();
        $finance = User::factory()->create();
        $payment = PaymentRequest::factory()->pending()->create(['user_id' => $employee->id]);

        $result = (new ApprovePaymentRequestAction())->handle($payment, $finance);

        $this->assertSame(PaymentStatus::Approved, $result->status);
        $this->assertSame($finance->id, $result->reviewed_by);
        $this->assertNotNull($result->reviewed_at);

        $this->assertDatabaseHas('payment_requests', [
            'id' => $payment->id,
            'status' => 'approved',
            'reviewed_by' => $finance->id,
        ]);
    }
}
