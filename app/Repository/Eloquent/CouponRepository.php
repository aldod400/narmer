<?php

namespace App\Repository\Eloquent;

use App\Repository\Contracts\CouponRepositoryInterface;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

class CouponRepository implements CouponRepositoryInterface
{
    public function getCouponByCode($code)
    {
        return Coupon::where('code', $code)->first();
    }
    public function getCouponUsageCount(int $couponId): int
    {
        return DB::table('user_coupons')
            ->where('coupon_id', $couponId)
            ->whereNotNull('used_at')
            ->count();
    }

    public function isCouponUsedByUser(int $couponId, int $userId): bool
    {
        return DB::table('user_coupons')
            ->where('coupon_id', $couponId)
            ->where('user_id', $userId)
            ->whereNotNull('used_at')
            ->exists();
    }
    public function markCouponAsUsed(int $couponId, int $userId)
    {
        return DB::table('user_coupons')->insert([
            'coupon_id' => $couponId,
            'user_id' => $userId,
            'used_at' => now(),
            'updated_at' => now(),
            'created_at' => now(),
        ]);
    }
}
