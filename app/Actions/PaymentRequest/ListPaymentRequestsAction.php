<?php

declare(strict_types=1);

namespace App\Actions\PaymentRequest;

use App\Enums\Role;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPaymentRequestsAction
{
    public function handle(User $user, ?string $status = null): LengthAwarePaginator
    {
        $query = PaymentRequest::query()
            ->with(['user', 'reviewer'])
            ->latest();

        // Finance sees every request; employees see only their own.
        if (! $user->hasRole(Role::Finance->value)) {
            $query->where('user_id', $user->id);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->paginate(15);
    }
}
