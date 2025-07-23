<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\CouponServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CouponController extends Controller
{
    protected $couponService;
    public function __construct(CouponServiceInterface $couponService)
    {
        $this->couponService = $couponService;
    }
    public function validateCoupon(string $code)
    {
        $result = $this->couponService->validateCoupon(
            $code,
            auth('api')->user()->id
        );

        if (!$result['success'])
            return Response::api($result['message'], 400, false, 400);

        return Response::api($result['message'], 200, true, null, $result['data']);
    }
}
