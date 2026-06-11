<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use RuntimeException;

class PaymentNotPendingException extends RuntimeException
{
    public function __construct(string $message = 'This payment request cannot be approved or rejected because it is not pending.')
    {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], Response::HTTP_CONFLICT);
    }
}
