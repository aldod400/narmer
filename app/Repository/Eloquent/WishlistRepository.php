<?php

namespace App\Repository\Eloquent;

use App\Models\Wishlist;
use App\Repository\Contracts\WishlistRepositoryInterface;

class WishlistRepository implements WishlistRepositoryInterface
{
    public function find(int $userId, int $productId)
    {
        return Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)->first();
    }
    public function getAllByUserId(int $userId)
    {
        return Wishlist::with(['product' => function ($query) {
            $query->with(['images' => function ($query) {
                $query->take(1);
            }])->select(
                'id',
                app()->getLocale() === 'ar' ? 'name_ar as name' : 'name_en as name',
                'slug',
                'description',
                'price',
                'discount_price',
                'quantity',
                'status',
                'category_id',
                'brand_id'
            );
        }])->where("user_id", $userId)->get();
    }
    public function create(int $userId, int $productId)
    {
        return Wishlist::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);
    }
    public function delete(int $userId, int $productId)
    {
        $wishlist = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
        $wishlist->delete();

        return $wishlist;
    }
}
