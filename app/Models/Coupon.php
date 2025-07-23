<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \App\Enums\DiscountType;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'expiry_date',
        'usage_limit',
    ];
    protected $casts = [
        'discount_type' => DiscountType::class,
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_coupons')
            ->withPivot('user_id', 'coupon_id', 'used_at')
            ->withTimestamps();
    }
}
