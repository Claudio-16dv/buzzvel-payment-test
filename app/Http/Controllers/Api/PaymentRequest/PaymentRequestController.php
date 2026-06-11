<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\PaymentRequest;

use App\Actions\PaymentRequest\ApprovePaymentRequestAction;
use App\Actions\PaymentRequest\CreatePaymentRequestAction;
use App\Actions\PaymentRequest\ListPaymentRequestsAction;
use App\Actions\PaymentRequest\RejectPaymentRequestAction;
use App\Actions\PaymentRequest\ShowPaymentRequestAction;
use App\DataTransferObjects\CreatePaymentRequestData;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest\IndexPaymentRequest;
use App\Http\Requests\PaymentRequest\StorePaymentRequest;
use App\Http\Resources\PaymentRequestResource;
use App\Models\PaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PaymentRequestController extends Controller
{
    #[OA\Get(
        path: '/api/payment-requests',
        summary: 'List payment requests',
        description: 'Employees see their own requests; finance sees all.',
        tags: ['Payment Requests'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter by status',
                schema: new OA\Schema(type: 'string', enum: ['pending', 'approved', 'rejected', 'expired']),
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of payment requests'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Invalid status filter'),
        ],
    )]
    public function index(IndexPaymentRequest $request, ListPaymentRequestsAction $action): JsonResponse
    {
        $payments = $action->handle($request->user(), $request->validated('status'));

        return PaymentRequestResource::collection($payments)->response();
    }

    #[OA\Post(
        path: '/api/payment-requests',
        summary: 'Create payment request',
        description: 'Exchange rate is fetched automatically at creation time.',
        tags: ['Payment Requests'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['amount', 'currency'],
                properties: [
                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 500.00),
                    new OA\Property(property: 'currency', type: 'string', example: 'BRL'),
                    new OA\Property(property: 'description', type: 'string', example: 'Office supplies'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Payment request created'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function store(StorePaymentRequest $request, CreatePaymentRequestAction $action): JsonResponse
    {
        $this->authorize('create', PaymentRequest::class);

        $payment = $action->handle(CreatePaymentRequestData::fromRequest($request));

        return (new PaymentRequestResource($payment->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/payment-requests/{paymentRequest}',
        summary: 'Show payment request',
        description: 'View a single payment request (owner or finance).',
        tags: ['Payment Requests'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'paymentRequest', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Payment request details'),
            new OA\Response(response: 403, description: 'Not allowed to view this request'),
            new OA\Response(response: 404, description: 'Not found'),
        ],
    )]
    public function show(Request $request, PaymentRequest $paymentRequest, ShowPaymentRequestAction $action): PaymentRequestResource
    {
        $this->authorize('view', $paymentRequest);

        return new PaymentRequestResource($action->handle($paymentRequest));
    }

    #[OA\Patch(
        path: '/api/payment-requests/{paymentRequest}/approve',
        summary: 'Approve payment request',
        description: 'Approve a pending payment request (finance only).',
        tags: ['Payment Requests'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'paymentRequest', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Payment request approved'),
            new OA\Response(response: 403, description: 'Not finance'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 409, description: 'Payment request is not pending'),
        ],
    )]
    public function approve(Request $request, PaymentRequest $paymentRequest, ApprovePaymentRequestAction $action): PaymentRequestResource
    {
        $this->authorize('approve', $paymentRequest);

        return new PaymentRequestResource($action->handle($paymentRequest, $request->user()));
    }

    #[OA\Patch(
        path: '/api/payment-requests/{paymentRequest}/reject',
        summary: 'Reject payment request',
        description: 'Reject a pending payment request (finance only).',
        tags: ['Payment Requests'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'paymentRequest', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Payment request rejected'),
            new OA\Response(response: 403, description: 'Not finance'),
            new OA\Response(response: 404, description: 'Not found'),
            new OA\Response(response: 409, description: 'Payment request is not pending'),
        ],
    )]
    public function reject(Request $request, PaymentRequest $paymentRequest, RejectPaymentRequestAction $action): PaymentRequestResource
    {
        $this->authorize('reject', $paymentRequest);

        return new PaymentRequestResource($action->handle($paymentRequest, $request->user()));
    }
}
