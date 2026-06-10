<?php

declare(strict_types=1);

namespace App\Actions\PaymentRequest;

use App\Enums\PaymentStatus;
use App\Models\PaymentRequest;
use Carbon\CarbonImmutable;

class ExpireStalePaymentRequestsAction
{
    private const STALE_AFTER_HOURS = 48;

    /**
     * Expire every pending request created more than 48 hours ago.
     *
     * @return int number of expired requests
     */
    public function handle(): int
    {
        $threshold = CarbonImmutable::now()->subHours(self::STALE_AFTER_HOURS);

        return PaymentRequest::query()
            ->where('status', PaymentStatus::Pending)
            ->where('created_at', '<=', $threshold)
            ->update(['status' => PaymentStatus::Expired]);
    }
}
