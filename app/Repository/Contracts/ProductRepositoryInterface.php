<?php

namespace App\Repository\Contracts;

interface ProductRepositoryInterface
{
    public function find(int $id);
    public function paginate(?string $search = null, int $perPage);
    public function getBestSelling(int $take);
    public function latest(int $take);
    public function getProductsByCategoryId(int $categoryId, int $perPage);
    public function getProductsByCategoryIds(array $ids, int $perPage);
    public function productDetails(int $id);
}
