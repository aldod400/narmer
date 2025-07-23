<?php

namespace App\Services\Implementations;

use App\Repository\Contracts\BannerRepositoryInterface;
use App\Services\Contracts\BannerServiceInterface;

class BannerService implements BannerServiceInterface
{
    protected $bannerRepo;
    public function __construct(BannerRepositoryInterface $bannerRepo)
    {
        $this->bannerRepo = $bannerRepo;
    }
    public function getBanners()
    {
        return $this->bannerRepo->all();
    }
}
