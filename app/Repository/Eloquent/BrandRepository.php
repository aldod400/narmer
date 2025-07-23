<?php

namespace App\Repository\Eloquent;

use App\Models\Brand;
use App\Repository\Contracts\BrandRepositoryInterface;

class BrandRepository implements BrandRepositoryInterface
{
    public function latest(int $take)
    {
        return Brand::latest()
            ->take($take)
            ->select(
                'id',
                app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name',
                'image',
            )
            ->get();
    }
    public function all()
    {
        return Brand::select(
            'id',
            app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name',
            'image',
        )->get();
    }
}
