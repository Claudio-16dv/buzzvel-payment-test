<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Multi-Currency Payment API',
    description: 'API for managing multi-currency payment requests with role-based approval and real-time exchange rates.',
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Local environment',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    description: 'Sanctum personal access token. Paste the token returned by /api/login.',
)]
#[OA\Tag(name: 'Authentication', description: 'Register, login and logout')]
#[OA\Tag(name: 'Payment Requests', description: 'Create, list, view and review payment requests')]
#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Ana Costa'),
        new OA\Property(property: 'email', type: 'string', example: 'ana@example.com'),
        new OA\Property(property: 'country', type: 'string', example: 'Brazil'),
        new OA\Property(property: 'currency', type: 'string', example: 'BRL'),
        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['employee']),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-06-11T13:00:00.000000Z'),
    ],
)]
#[OA\Schema(
    schema: 'AuthResponse',
    properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
        new OA\Property(property: 'token', type: 'string', example: '1|aBcDeFgHiJkLmNoPqRsTuVwXyZ0123456789'),
    ],
)]
#[OA\Schema(
    schema: 'PaymentRequest',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'amount', type: 'string', example: '500.00'),
        new OA\Property(property: 'currency', type: 'string', example: 'BRL'),
        new OA\Property(property: 'exchange_rate', type: 'string', example: '5.98000000'),
        new OA\Property(property: 'amount_in_eur', type: 'string', example: '83.61'),
        new OA\Property(property: 'rate_source', type: 'string', example: 'exchangerate-api.com'),
        new OA\Property(property: 'rate_fetched_at', type: 'string', format: 'date-time', example: '2026-06-11T13:00:00.000000Z'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'approved', 'rejected', 'expired'], example: 'pending'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Office supplies'),
        new OA\Property(property: 'reviewed_at', type: 'string', format: 'date-time', nullable: true, example: null),
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
        new OA\Property(property: 'reviewer', ref: '#/components/schemas/User', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-06-11T13:00:00.000000Z'),
    ],
)]
#[OA\Schema(
    schema: 'ValidationError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(property: 'errors', type: 'object', example: ['email' => ['The email field is required.']]),
    ],
)]
#[OA\Schema(
    schema: 'MessageResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Logged out successfully.'),
    ],
)]
abstract class OpenApi
{
}
