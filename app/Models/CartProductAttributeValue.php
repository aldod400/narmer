<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartProductAttributeValue extends Model
{
    protected $fillable = [
        "cart_id",
        "product_attribute_value_id",
    ];
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
    public function productAttributeValue()
    {
        return $this->belongsTo(ProductAttributeValue::class);
    }
}
