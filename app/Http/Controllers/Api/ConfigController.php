<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Contracts\ConfigServiceInterface;
use Illuminate\Http\Response;

class ConfigController extends Controller
{
    protected $configService;

    public function __construct(ConfigServiceInterface $configService)
    {
        $this->configService = $configService;
    }

    public function getConfig()
    {
        $config = $this->configService->getConfig();
        return Response::api(__('message.Success'), 200, true, null, $config);
    }
}
