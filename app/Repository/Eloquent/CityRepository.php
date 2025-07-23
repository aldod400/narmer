<?php

namespace App\Repository\Eloquent;

use App\Models\City;
use App\Repository\Contracts\CityRepositoryInterface;

class CityRepository implements CityRepositoryInterface
{
    public function getAllCities()
    {
        return City::select(
            'id',
            app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name'
        )->get();
    }
    public function getAllCitiesWithAreas()
    {
        return City::with(['areas' => function ($query) {
            $query->select(
                'id',
                app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name',
                'price',
                'city_id',
            );
        }])->select(
            'id',
            app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name'
        )->get();
    }
    public function getCityWithAreas(int $cityId)
    {
        return City::with(['areas' => function ($query) {
            $query->select(
                'id',
                app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name',
                'price',
                'city_id',
            );
        }])->select(
            'id',
            app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name'
        )->find($cityId);
    }
}
