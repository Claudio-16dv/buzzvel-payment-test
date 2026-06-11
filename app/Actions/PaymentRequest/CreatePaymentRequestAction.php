<?php

declare(strict_types=1);

namespace App\Actions\PaymentRequest;

use App\DTO\CreatePaymentRequestData;
use App\Enums\PaymentStatus;
use App\Models\PaymentRequest;
use App\Services\ExchangeRateService;

class CreatePaymentRequestAction
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRates,
    ) {
    }

    public function handle(CreatePaymentRequestData $data): PaymentRequest
    {
        $base = config('currencies.base');
        $rate = $this->exchangeRates->getRate($base, $data->currency);

        // Convert the local amount back to the base currency (EUR).
        $amountInEur = round($data->amount / $rate->rate, 2);

        return PaymentRequest::create([
            'user_id' => $data->userId,
            'amount' => $data->amount,
            'currency' => $data->currency,
            'exchange_rate' => $rate->rate,
            'amount_in_eur' => $amountInEur,
            'rate_source' => $rate->source,
            'rate_fetched_at' => $rate->fetchedAt,
            'status' => PaymentStatus::Pending,
            'description' => $data->description,
        ]);
    }
}
