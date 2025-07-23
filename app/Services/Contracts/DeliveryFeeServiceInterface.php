<?php

namespace App\Services\Contracts;

interface DeliveryFeeServiceInterface
{
    public function calculateDeliveryFee(int $addressId,  ?int $areaId);
}
