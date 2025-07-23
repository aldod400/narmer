<?php

namespace App\Helpers;

class PriceHelpers
{
    public static function calcolatePriceForCart($carts)
    {
        $totalPrice = 0;
        $totalPriceAfterDiscount = 0;

        foreach ($carts as $cart) {
            $attributesPrice = 0;
            if (!empty($cart->ProductAttributeValues)) {
                foreach ($cart->ProductAttributeValues as $attr) {
                    $attributesPrice += $attr->price;
                }
            }

            $totalPrice += ($cart->product->price * $cart->quantity) + ($attributesPrice * $cart->quantity);

            $priceAfterDiscount = $cart->product->discount_price != 0 ? $cart->product->discount_price : $cart->product->price;
            $totalPriceAfterDiscount += ($priceAfterDiscount * $cart->quantity) + ($attributesPrice * $cart->quantity);
        }
        $data = [];
        $data['total_price'] = $totalPrice;
        $data['total_price_after_discount'] = $totalPriceAfterDiscount;
        $data['discount'] = $totalPrice - $totalPriceAfterDiscount;
        return $data;
    }
}
