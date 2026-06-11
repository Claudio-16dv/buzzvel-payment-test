<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\LogoutAction;
use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/login',
        summary: 'Login',
        description: 'Authenticate a user and return an access token. Start here.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'finance@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthResponse'),
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid credentials',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
            ),
        ],
    )]
    public function login(LoginRequest $request, LoginAction $action): JsonResponse
    {
        $result = $action->handle($request->validated());

        return response()->json([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ]);
    }

    #[OA\Post(
        path: '/api/register',
        summary: 'Register',
        description: 'Register a new user and return an access token.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation', 'country', 'currency'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Ana Costa'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ana@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(property: 'country', type: 'string', example: 'Brazil'),
                    new OA\Property(property: 'currency', type: 'string', example: 'BRL'),
                    new OA\Property(property: 'role', type: 'string', enum: ['employee', 'finance'], example: 'employee'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthResponse'),
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
            ),
        ],
    )]
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $user = $action->handle($request->validated());
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    #[OA\Post(
        path: '/api/logout',
        summary: 'Logout',
        description: 'Revoke the current access token.',
        tags: ['Authentication'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Logged out'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function logout(Request $request, LogoutAction $action): JsonResponse
    {
        $action->handle($request->user());

        return response()->json(['message' => 'Logged out successfully.']);
    }

    #[OA\Get(
        path: '/api/me',
        summary: 'Current user',
        description: 'Get the authenticated user profile.',
        tags: ['Authentication'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Current user'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user()->load('roles'));
    }
}
