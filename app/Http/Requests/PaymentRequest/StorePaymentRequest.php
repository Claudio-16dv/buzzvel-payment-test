<?php

declare(strict_types=1);

namespace App\Http\Requests\PaymentRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates payment request creation. Exchange data is never accepted from
 * the client; it is fetched and computed server-side.
 */
class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'currency' => ['required', 'string', 'size:3', Rule::in(config('currencies.supported'))],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
