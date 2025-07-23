<?php

namespace App\Repository\Contracts;

interface BrandRepositoryInterface
{
    public function latest(int $take);
    public function all();
}
