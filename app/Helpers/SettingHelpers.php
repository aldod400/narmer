<?php

namespace App\Helpers;

use App\Models\Setting;

class SettingHelpers
{
    public static function get($key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value)
    {
        return Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
