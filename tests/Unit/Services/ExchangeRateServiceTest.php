<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Exceptions\ExchangeRateException;
use App\Services\ExchangeRateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeRateServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_it_fetches_the_rate_for_a_target_currency(): void
    {
        Http::fake([
            '*' => Http::response(['base' => 'EUR', 'rates' => ['BRL' => 5.5]]),
        ]);

        $rate = app(ExchangeRateService::class)->getRate('EUR', 'BRL');

        $this->assertSame('EUR', $rate->base);
        $this->assertSame('BRL', $rate->target);
        $this->assertSame(5.5, $rate->rate);
        $this->assertSame('exchangerate-api.com', $rate->source);
    }

    public function test_it_returns_rate_of_one_for_same_currency(): void
    {
        Http::fake();

        $rate = app(ExchangeRateService::class)->getRate('EUR', 'EUR');

        $this->assertSame(1.0, $rate->rate);
        Http::assertNothingSent();
    }

    public function test_it_caches_the_rates_and_avoids_repeated_calls(): void
    {
        Http::fake([
            '*' => Http::response(['base' => 'EUR', 'rates' => ['BRL' => 5.5, 'USD' => 1.08]]),
        ]);

        $service = app(ExchangeRateService::class);
        $service->getRate('EUR', 'BRL');
        $service->getRate('EUR', 'USD');

        Http::assertSentCount(1);
    }

    public function test_it_throws_when_currency_is_missing_in_response(): void
    {
        Http::fake([
            '*' => Http::response(['base' => 'EUR', 'rates' => ['USD' => 1.08]]),
        ]);

        $this->expectException(ExchangeRateException::class);

        app(ExchangeRateService::class)->getRate('EUR', 'BRL');
    }

    public function test_it_throws_when_the_provider_fails(): void
    {
        Http::fake([
            '*' => Http::response([], 500),
        ]);

        $this->expectException(ExchangeRateException::class);

        app(ExchangeRateService::class)->getRate('EUR', 'BRL');
    }
}
