<?php

namespace App\Services\Contracts;

interface CouponServiceInterface
{
    public function validateCoupon(string $code, int $userId);
}
