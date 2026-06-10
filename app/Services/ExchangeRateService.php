<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\ExchangeRate;
use App\Exceptions\ExchangeRateException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class ExchangeRateService
{
    private const SOURCE = 'exchangerate-api.com';

    public function getRate(string $base, string $target): ExchangeRate
    {
        $base = strtoupper($base);
        $target = strtoupper($target);

        $rate = $base === $target
            ? 1.0
            : (float) ($this->fetchRates($base)[$target] ?? 0.0);

        if ($rate <= 0) {
            throw ExchangeRateException::rateNotFound($target);
        }

        return new ExchangeRate(
            base: $base,
            target: $target,
            rate: $rate,
            source: self::SOURCE,
            fetchedAt: CarbonImmutable::now(),
        );
    }

    /**
     * Fetch (and cache) the full rate table for a base currency.
     *
     * @return array<string, float>
     */
    private function fetchRates(string $base): array
    {
        $ttl = (int) config('services.exchange_rate.cache_ttl', 3600);

        return Cache::remember("exchange_rates:{$base}", $ttl, function () use ($base): array {
            try {
                $response = Http::acceptJson()
                    ->timeout(10)
                    ->get(config('services.exchange_rate.url')."/{$base}");
            } catch (Throwable) {
                throw ExchangeRateException::unavailable($base, 'requested currency');
            }

            if ($response->failed()) {
                throw ExchangeRateException::unavailable($base, 'requested currency');
            }

            /** @var array<string, float> $rates */
            $rates = $response->json('rates', []);

            return $rates;
        });
    }
}
