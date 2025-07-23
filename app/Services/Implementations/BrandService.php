<?php

namespace App\Services\Implementations;

use App\Repository\Contracts\BrandRepositoryInterface;
use App\Services\Contracts\BrandServiceInterface;

class BrandService implements BrandServiceInterface
{
    protected $brandRepo;
    public function __construct(BrandRepositoryInterface $brandRepo)
    {
        $this->brandRepo = $brandRepo;
    }
    public function getBrands(int $take)
    {
        return $this->brandRepo->latest($take);
    }
}
