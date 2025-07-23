<?php

namespace App\Services\Contracts;

interface PaymobServiceInterface
{
    public function generatePaymentLink(
        string $title,
        string $paid_type,
        float $price,
        array $customerInfo = [],
        string $paymentMethod = 'card',
        ?string $walletNumber = null
    );
    public function paymentCallback(array $data);
}
