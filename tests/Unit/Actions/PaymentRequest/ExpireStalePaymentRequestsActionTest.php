<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\PaymentRequest;

use App\Actions\PaymentRequest\ExpireStalePaymentRequestsAction;
use App\Enums\PaymentStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireStalePaymentRequestsActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_expires_only_pending_requests_older_than_48_hours(): void
    {
        $user = User::factory()->create();

        $stale = PaymentRequest::factory()->pending()->create(['user_id' => $user->id]);
        $stale->forceFill(['created_at' => now()->subHours(49)])->save();

        $recent = PaymentRequest::factory()->pending()->create(['user_id' => $user->id]);
        $recent->forceFill(['created_at' => now()->subHours(2)])->save();

        $oldApproved = PaymentRequest::factory()->approved()->create(['user_id' => $user->id]);
        $oldApproved->forceFill(['created_at' => now()->subHours(72)])->save();

        $count = (new ExpireStalePaymentRequestsAction())->handle();

        $this->assertSame(1, $count);
        $this->assertSame(PaymentStatus::Expired, $stale->fresh()->status);
        $this->assertSame(PaymentStatus::Pending, $recent->fresh()->status);
        $this->assertSame(PaymentStatus::Approved, $oldApproved->fresh()->status);
    }

    public function test_it_returns_zero_when_there_is_nothing_to_expire(): void
    {
        $user = User::factory()->create();
        PaymentRequest::factory()->pending()->create(['user_id' => $user->id]);

        $count = (new ExpireStalePaymentRequestsAction())->handle();

        $this->assertSame(0, $count);
    }
}
