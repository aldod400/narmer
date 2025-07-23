<?php

namespace App\Services\Implementations;

use App\Repository\Contracts\BrandRepositoryInterface;
use App\Repository\Contracts\CityRepositoryInterface;
use App\Services\Contracts\ConfigServiceInterface;
use App\Repository\Contracts\ConfigRepositoryInterface;
use App\Repository\Contracts\SettingRepositoryInterface;

class ConfigService implements ConfigServiceInterface
{
    protected $configRepository;
    protected $cityRepo;
    protected $settingRepo;
    protected $brandRepo;

    public function __construct(
        ConfigRepositoryInterface $configRepository,
        CityRepositoryInterface $cityRepo,
        SettingRepositoryInterface $settingRepo,
        BrandRepositoryInterface $brandRepo
    ) {
        $this->configRepository = $configRepository;
        $this->cityRepo = $cityRepo;
        $this->settingRepo = $settingRepo;
        $this->brandRepo = $brandRepo;
    }

    public function getConfig()
    {
        $config = $this->configRepository->getConfig();
        $deliveryman = $this->settingRepo->getDeliverymanValue();
        $settings = null;
        $cities = null;
        $area = false;
        $fixed = false;
        $km = false;
        if ($deliveryman) {
            $config['deliveryman'] = true;
            $settings = $this->settingRepo->getAll();
            foreach ($settings as $setting) {
                if ($setting->key == 'delivery_fee_type' && $setting->value == 'area') {
                    $area = true;
                } elseif ($setting->key == 'delivery_fee_type' && $setting->value == 'per_km') {
                    $km = true;
                } elseif ($setting->key == 'delivery_fee_type' && $setting->value == 'fixed') {
                    $fixed = true;
                }
            }
        } else
            $config['deliveryman'] = false;

        if ($fixed) {
            $config['delivery_fee_type'] = 'fixed';
            foreach ($settings as $setting) {
                if ($setting->key == 'delivery_fee_fixed') {
                    $config['delivery_fee'] = $setting->value;
                }
            }
        } elseif ($km) {
            $config['delivery_fee_type'] = 'per_km';
            foreach ($settings as $setting) {
                if ($setting->key == 'delivery_fee_per_km') {
                    $config['delivery_fee'] = $setting->value;
                }
            }
        } elseif ($area)
            $config['delivery_fee_type'] = 'area';

        if ($area)
            $cities = $this->cityRepo->getAllCitiesWithAreas();
        else
            $cities = $this->cityRepo->getAllCities();

        $brands = $this->brandRepo->all();

        return [
            'config' => $config,
            'cities' => $cities,
            'brands' => $brands,
        ];
    }
}
