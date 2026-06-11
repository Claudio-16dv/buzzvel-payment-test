<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\PaymentRequest;

use App\Actions\PaymentRequest\CreatePaymentRequestAction;
use App\DTO\CreatePaymentRequestData;
use App\DTO\ExchangeRate;
use App\Enums\PaymentStatus;
use App\Models\User;
use App\Services\ExchangeRateService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CreatePaymentRequestActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_payment_with_converted_eur_amount(): void
    {
        $user = User::factory()->create();

        $fakeRate = new ExchangeRate(
            base: 'EUR',
            target: 'BRL',
            rate: 5.0,
            source: 'exchangerate-api.com',
            fetchedAt: CarbonImmutable::now(),
        );

        $service = Mockery::mock(ExchangeRateService::class);
        $service->shouldReceive('getRate')->once()->with('EUR', 'BRL')->andReturn($fakeRate);

        $data = new CreatePaymentRequestData(
            userId: $user->id,
            amount: 500.0,
            currency: 'BRL',
            description: 'Test payment',
        );

        $payment = (new CreatePaymentRequestAction($service))->handle($data);

        $this->assertSame('500.00', $payment->amount);
        $this->assertSame('BRL', $payment->currency);
        $this->assertSame('5.00000000', $payment->exchange_rate);
        // 500 / 5.0 = 100.00 EUR
        $this->assertSame('100.00', $payment->amount_in_eur);
        $this->assertSame('exchangerate-api.com', $payment->rate_source);
        $this->assertSame(PaymentStatus::Pending, $payment->status);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
