<?php

namespace App\Repository\Contracts;

interface CartRepositoryInterface
{
    public function find(int $id);
    public function getUserCart(int $userId);
    public function create(array $data);
    public function update(int $id, int $quantity);
    public function delete(int $id);
}
