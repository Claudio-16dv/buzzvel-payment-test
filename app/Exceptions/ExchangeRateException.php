<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class ExchangeRateException extends RuntimeException
{
    public static function unavailable(string $base, string $target): self
    {
        return new self("Unable to fetch exchange rate for {$base} to {$target}.");
    }

    public static function rateNotFound(string $target): self
    {
        return new self("Exchange rate for currency [{$target}] was not found in the provider response.");
    }
}
