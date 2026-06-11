<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\PaymentRequest;

use App\Actions\PaymentRequest\RejectPaymentRequestAction;
use App\Enums\PaymentStatus;
use App\Exceptions\PaymentNotPendingException;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RejectPaymentRequestActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_the_request_as_rejected_and_records_the_reviewer(): void
    {
        $employee = User::factory()->create();
        $finance = User::factory()->create();
        $payment = PaymentRequest::factory()->pending()->create(['user_id' => $employee->id]);

        $result = (new RejectPaymentRequestAction())->handle($payment, $finance);

        $this->assertSame(PaymentStatus::Rejected, $result->status);
        $this->assertSame($finance->id, $result->reviewed_by);
        $this->assertNotNull($result->reviewed_at);

        $this->assertDatabaseHas('payment_requests', [
            'id' => $payment->id,
            'status' => 'rejected',
            'reviewed_by' => $finance->id,
        ]);
    }

    public function test_it_throws_when_the_request_is_not_pending(): void
    {
        $payment = PaymentRequest::factory()->rejected()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        $this->expectException(PaymentNotPendingException::class);

        (new RejectPaymentRequestAction())->handle($payment, User::factory()->create());
    }
}
