<?php

namespace App\Repository\Eloquent;

use App\Models\Config;
use App\Repository\Contracts\ConfigRepositoryInterface;

class ConfigRepository implements ConfigRepositoryInterface
{
    public function getConfig()
    {
        return Config::first();
    }
}
