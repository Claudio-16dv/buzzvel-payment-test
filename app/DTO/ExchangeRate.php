<?php

declare(strict_types=1);

namespace App\DTO;

use Carbon\CarbonImmutable;

final readonly class ExchangeRate
{
    public function __construct(
        public string $base,
        public string $target,
        public float $rate,
        public string $source,
        public CarbonImmutable $fetchedAt,
    ) {
    }
}
