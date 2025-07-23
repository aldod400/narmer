<?php

namespace App\Repository\Contracts;

interface CityRepositoryInterface
{
    public function getAllCities();
    public function getAllCitiesWithAreas();
    public function getCityWithAreas(int $cityId);
}
