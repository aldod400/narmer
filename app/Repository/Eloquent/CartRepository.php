<?php

namespace App\Repository\Eloquent;

use App\Models\Cart;
use App\Repository\Contracts\CartRepositoryInterface;

class CartRepository implements CartRepositoryInterface
{
    public function find(int $id)
    {
        return Cart::findOrFail($id);
    }
    public function getUserCart(int $userId)
    {
        return Cart::with(['ProductAttributeValues', 'product' => function ($query) {
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
        }, 'ProductAttributeValues.attributeValue.attribute' => function ($query) {
            $query->select('id', app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name');
        }])->where('user_id', $userId)->get();
    }
    public function create(array $data)
    {
        return Cart::create($data);
    }
    public function update(int $id, int $quantity)
    {
        $cart = Cart::findOrFail($id);
        $cart->update([
            'quantity' => $quantity
        ]);
        return $cart->refresh();
    }
    public function delete(int $id)
    {
        $cart = Cart::findOrFail($id);
        $cart->delete();

        return true;
    }
}
