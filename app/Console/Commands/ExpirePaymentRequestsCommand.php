<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\PaymentRequest\ExpireStalePaymentRequestsAction;
use Illuminate\Console\Command;

class ExpirePaymentRequestsCommand extends Command
{
    protected $signature = 'payment-requests:expire';

    protected $description = 'Expire payment requests pending for more than 48 hours';

    public function handle(ExpireStalePaymentRequestsAction $action): int
    {
        $count = $action->handle();

        $this->info("Expired {$count} stale payment request(s).");

        return self::SUCCESS;
    }
}
