<?php

namespace App\Repository\Eloquent;

use App\Models\PaymobPayment;
use App\Repository\Contracts\PaymobPaymentRepositoryInterface;

class PaymobPaymentRepository implements PaymobPaymentRepositoryInterface
{
    public function create(array $data)
    {
        return PaymobPayment::create($data);
    }
    public function getPaymentByOrderId(int $orderId)
    {
        return PaymobPayment::where('order_id', $orderId)->first();
    }
    public function update(int $orderId, array $data)
    {
        $payment = PaymobPayment::where('order_id', $orderId)->first();
        $payment->update($data);
        return $payment;
    }
}
