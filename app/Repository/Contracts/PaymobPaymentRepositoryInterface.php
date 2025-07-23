<?php

namespace App\Repository\Contracts;

interface PaymobPaymentRepositoryInterface
{
    public function create(array $data);
    public function getPaymentByOrderId(int $orderId);
    public function update(int $orderId, array $data);
}
