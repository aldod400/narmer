<?php

namespace App\Repository\Contracts;

interface CouponRepositoryInterface
{
    public function getCouponByCode(string $code);
    public function getCouponUsageCount(int $couponId);
    public function isCouponUsedByUser(int $couponId, int $userId);
    public function markCouponAsUsed(int $couponId, int $userId);
}
