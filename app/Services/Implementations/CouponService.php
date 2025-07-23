<?php

namespace App\Services\Implementations;

use App\Repository\Contracts\CouponRepositoryInterface;
use App\Services\Contracts\CouponServiceInterface;

class CouponService implements CouponServiceInterface
{
    protected $couponRepo;
    public function __construct(CouponRepositoryInterface $couponRepo)
    {
        $this->couponRepo = $couponRepo;
    }
    public function validateCoupon(string $code, int $userId)
    {
        $coupon = $this->couponRepo->getCouponByCode($code);

        if (!$coupon)
            return ['success' => false, 'message' => __('message.Coupon not found')];

        if ($coupon->expiry_date && now()->greaterThan($coupon->expiry_date)) {
            return [
                'success' => false,
                'message' => __('message.Coupon has expired'),
            ];
        }
        if ($this->couponRepo->isCouponUsedByUser($coupon->id, $userId)) {
            return [
                'success' => false,
                'message' => __('message.Coupon has already been used by you'),
            ];
        }

        $totalUsed = $this->couponRepo->getCouponUsageCount($coupon->id);

        if ($coupon->usage_limit && $totalUsed >= $coupon->usage_limit) {
            return [
                'success' => false,
                'message' => __('message.Coupon has reached its usage limit'),
            ];
        }
        return [
            'success' => true,
            'message' => __('message.Success'),
            'data' => $coupon
        ];
    }
}
