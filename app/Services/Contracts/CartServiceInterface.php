<?php

namespace App\Services\Contracts;

interface CartServiceInterface
{
    public function getCart();
    public function create(array $data);
    public function update(int $id, int $quantity);
    public function delete(int $id);
    public function getDeliveryFee(int $addressId);
}
