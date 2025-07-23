<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetailAttributeValue extends Model
{
    protected $fillable = [
        "order_detail_id",
        "attribute_value_id",
    ];
    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class);
    }
    public function attributeValue()
    {
        return $this->belongsTo(AttributeValue::class);
    }
}
