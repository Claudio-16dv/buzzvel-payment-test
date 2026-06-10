<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\PaymentRequest;

use App\Actions\PaymentRequest\ListPaymentRequestsAction;
use App\Models\PaymentRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithRoles;
use Tests\TestCase;

class ListPaymentRequestsActionTest extends TestCase
{
    use InteractsWithRoles;
    use RefreshDatabase;

    public function test_an_employee_only_gets_their_own_requests(): void
    {
        $employee = $this->createEmployee();
        $other = $this->createEmployee();

        PaymentRequest::factory()->count(2)->create(['user_id' => $employee->id]);
        PaymentRequest::factory()->count(3)->create(['user_id' => $other->id]);

        $result = (new ListPaymentRequestsAction())->handle($employee);

        $this->assertSame(2, $result->total());
    }

    public function test_finance_gets_every_request(): void
    {
        $employee = $this->createEmployee();
        $finance = $this->createFinance();

        PaymentRequest::factory()->count(4)->create(['user_id' => $employee->id]);

        $result = (new ListPaymentRequestsAction())->handle($finance);

        $this->assertSame(4, $result->total());
    }

    public function test_it_filters_by_status(): void
    {
        $employee = $this->createEmployee();

        PaymentRequest::factory()->pending()->count(2)->create(['user_id' => $employee->id]);
        PaymentRequest::factory()->approved()->count(3)->create(['user_id' => $employee->id]);

        $result = (new ListPaymentRequestsAction())->handle($employee, 'approved');

        $this->assertSame(3, $result->total());
    }
}
