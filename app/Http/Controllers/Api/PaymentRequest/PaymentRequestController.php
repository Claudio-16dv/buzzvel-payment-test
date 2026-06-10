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

class PaymentRequestController extends Controller
{
    public function index(IndexPaymentRequest $request, ListPaymentRequestsAction $action): JsonResponse
    {
        $payments = $action->handle($request->user(), $request->validated('status'));

        return PaymentRequestResource::collection($payments)->response();
    }

    public function store(StorePaymentRequest $request, CreatePaymentRequestAction $action): JsonResponse
    {
        $this->authorize('create', PaymentRequest::class);

        $payment = $action->handle(CreatePaymentRequestData::fromRequest($request));

        return (new PaymentRequestResource($payment->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, PaymentRequest $paymentRequest, ShowPaymentRequestAction $action): PaymentRequestResource
    {
        $this->authorize('view', $paymentRequest);

        return new PaymentRequestResource($action->handle($paymentRequest));
    }

    public function approve(Request $request, PaymentRequest $paymentRequest, ApprovePaymentRequestAction $action): PaymentRequestResource
    {
        $this->authorize('approve', $paymentRequest);

        return new PaymentRequestResource($action->handle($paymentRequest, $request->user()));
    }

    public function reject(Request $request, PaymentRequest $paymentRequest, RejectPaymentRequestAction $action): PaymentRequestResource
    {
        $this->authorize('reject', $paymentRequest);

        return new PaymentRequestResource($action->handle($paymentRequest, $request->user()));
    }
}
