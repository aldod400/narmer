<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymobPayment extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'payment_method',
        'status',
        'trnx_id',
        'order_id',
        'txn_response_code',
        'message',
        'pending',
        'success',
        'type',
        'source_data_sub_type',
        'my_order_id'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'my_order_id');
    }
}
