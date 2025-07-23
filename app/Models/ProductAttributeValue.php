<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeValue extends Model
{
    protected $fillable = [
        'product_id',
        'attribute_value_id',
        'price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValue()
    {
        return $this->belongsTo(AttributeValue::class, 'attribute_value_id');
    }

    public function attribute()
    {
        return $this->attributeValue ? $this->attributeValue->attribute : null;
    }
}
