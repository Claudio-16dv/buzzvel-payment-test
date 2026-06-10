<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\PaymentRequest;

use App\Actions\PaymentRequest\ShowPaymentRequestAction;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowPaymentRequestActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_request_with_relations_loaded(): void
    {
        $user = User::factory()->create();
        $payment = PaymentRequest::factory()->create(['user_id' => $user->id]);

        $result = (new ShowPaymentRequestAction())->handle($payment);

        $this->assertTrue($result->is($payment));
        $this->assertTrue($result->relationLoaded('user'));
        $this->assertTrue($result->relationLoaded('reviewer'));
    }
}
