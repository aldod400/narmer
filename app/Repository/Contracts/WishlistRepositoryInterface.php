<?php

namespace App\Repository\Contracts;

interface WishlistRepositoryInterface
{
    public function find(int $userId, int $productId);
    public function getAllByUserId(int $userId);
    public function create(int $userId, int $productId);
    public function delete(int $userId, int $productId);
}