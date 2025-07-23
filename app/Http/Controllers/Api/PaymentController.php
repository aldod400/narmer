<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaymobPaymentCallBackRequest;
use App\Services\Contracts\PaymobServiceInterface;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    protected $paymobService;
    public function __construct(PaymobServiceInterface $paymobService)
    {
        $this->paymobService = $paymobService;
    }
    public function callback(PaymobPaymentCallBackRequest $request)
    {
        $result = $this->paymobService->paymentCallback($request->validated());

        if (!$result['success'])
            return Response::api($result['message'], 400, false, 400);

        return Response::api($result['message'], 200, true, 200);
    }
}
