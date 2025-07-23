<?php

namespace App\Services\Contracts;

interface WishlistServiceInterface
{
    public function getAllByUserId(int $userId);
    public function add(int $userId, int $productId);
    public function remove(int $userId, int $productId);
}
