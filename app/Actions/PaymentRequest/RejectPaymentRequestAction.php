<?php

declare(strict_types=1);

namespace App\Actions\PaymentRequest;

use App\Enums\PaymentStatus;
use App\Exceptions\PaymentNotPendingException;
use App\Models\PaymentRequest;
use App\Models\User;
use Carbon\CarbonImmutable;

class RejectPaymentRequestAction
{
    public function handle(PaymentRequest $payment, User $reviewer): PaymentRequest
    {
        if (! $payment->status->isPending()) {
            throw new PaymentNotPendingException();
        }

        $payment->update([
            'status' => PaymentStatus::Rejected,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => CarbonImmutable::now(),
        ]);

        return $payment->fresh(['user', 'reviewer']);
    }
}
