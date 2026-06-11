<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\PaymentRequest;
use App\Models\User;

class PaymentRequestPolicy
{
    public function view(User $user, PaymentRequest $payment): bool
    {
        return $user->id === $payment->user_id
            || $user->hasRole(Role::Finance->value);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function approve(User $user, PaymentRequest $payment): bool
    {
        return $user->hasRole(Role::Finance->value);
    }

    public function reject(User $user, PaymentRequest $payment): bool
    {
        return $user->hasRole(Role::Finance->value);
    }
}
