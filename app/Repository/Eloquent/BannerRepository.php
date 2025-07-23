<?php

namespace App\Repository\Eloquent;

use App\Models\Banner;
use App\Repository\Contracts\BannerRepositoryInterface;

class BannerRepository implements BannerRepositoryInterface
{
    public function all()
    {
        return Banner::latest()->get();
    }
}
