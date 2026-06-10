<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\Http\Requests\PaymentRequest\StorePaymentRequest;

final readonly class CreatePaymentRequestData
{
    public function __construct(
        public int $userId,
        public float $amount,
        public string $currency,
        public ?string $description,
    ) {
    }

    public static function fromRequest(StorePaymentRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            userId: (int) $request->user()->id,
            amount: (float) $validated['amount'],
            currency: strtoupper($validated['currency']),
            description: $validated['description'] ?? null,
        );
    }
}
