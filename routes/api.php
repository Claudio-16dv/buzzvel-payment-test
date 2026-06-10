<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\PaymentRequest\PaymentRequestController;
use Illuminate\Support\Facades\Route;

// Public authentication routes.
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Authenticated routes (Sanctum token required).
Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::prefix('payment-requests')->group(function (): void {
        Route::get('/', [PaymentRequestController::class, 'index']);
        Route::post('/', [PaymentRequestController::class, 'store']);
        Route::get('/{paymentRequest}', [PaymentRequestController::class, 'show']);

        // Finance-only review actions: role barrier + Policy state check.
        Route::middleware('role:finance')->group(function (): void {
            Route::patch('/{paymentRequest}/approve', [PaymentRequestController::class, 'approve']);
            Route::patch('/{paymentRequest}/reject', [PaymentRequestController::class, 'reject']);
        });
    });
});
