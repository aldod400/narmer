<?php

namespace App\Repository\Eloquent;

use App\Models\Setting;
use App\Repository\Contracts\SettingRepositoryInterface;

class SettingRepository implements SettingRepositoryInterface
{
    public function getDeliverymanValue()
    {
        return Setting::where('key', 'deliveryman')->first()->value;
    }
    public function getAll()
    {
        return Setting::all();
    }
    public function getValue(string $key)
    {
        return Setting::where('key', $key)->first()->value;
    }
}
