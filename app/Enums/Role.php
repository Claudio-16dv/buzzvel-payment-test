<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case Employee = 'employee';
    case Finance = 'finance';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $role) => $role->value, self::cases());
    }
}
