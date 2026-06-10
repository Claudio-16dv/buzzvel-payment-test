<?php

declare(strict_types=1);

namespace App\Actions\PaymentRequest;

use App\Models\PaymentRequest;

class ShowPaymentRequestAction
{
    public function handle(PaymentRequest $payment): PaymentRequest
    {
        return $payment->load(['user', 'reviewer']);
    }
}
