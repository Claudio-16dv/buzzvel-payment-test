<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\PaymentRequest
 */
class PaymentRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'exchange_rate' => $this->exchange_rate,
            'amount_in_eur' => $this->amount_in_eur,
            'rate_source' => $this->rate_source,
            'rate_fetched_at' => $this->rate_fetched_at,
            'status' => $this->status->value,
            'description' => $this->description,
            'reviewed_at' => $this->reviewed_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'reviewer' => new UserResource($this->whenLoaded('reviewer')),
            'created_at' => $this->created_at,
        ];
    }
}
