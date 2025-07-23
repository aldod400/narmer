<?php

namespace App\Services\Implementations;

use App\Helpers\DestanceHelpers;
use App\Repository\Contracts\AddressRepositoryInterface;
use App\Repository\Contracts\CityRepositoryInterface;
use App\Repository\Contracts\SettingRepositoryInterface;
use App\Services\Contracts\DeliveryFeeServiceInterface;

class DeliveryFeeService implements DeliveryFeeServiceInterface
{
    protected $addressRepo;
    protected $settingRepo;
    protected $cityRepo;
    public function __construct(
        AddressRepositoryInterface $addressRepo,
        SettingRepositoryInterface $settingRepo,
        CityRepositoryInterface $cityRepo
    ) {
        $this->addressRepo = $addressRepo;
        $this->settingRepo = $settingRepo;
        $this->cityRepo = $cityRepo;
    }
    public function calculateDeliveryFee(int $addressId, ?int $areaId)
    {
        $address = $this->addressRepo->find($addressId);

        if (!$address)
            return [
                'success' => false,
                'message' => __('message.Address Not Found'),
            ];

        if ($this->settingRepo->getValue('deliveryman')) {
            $deliveryType = $this->settingRepo->getValue('delivery_fee_type');

            switch ($deliveryType) {
                case 'fixed':
                    return [
                        'success' => true,
                        'message' => __('message.Success'),
                        'data'    => (float) $this->settingRepo->getValue('delivery_fee_fixed'),
                    ];

                case 'per_km':
                    $latitude = $this->settingRepo->getValue('latitude');
                    $longitude = $this->settingRepo->getValue('longitude');
                    $distance = DestanceHelpers::getGoogleMapsDistanceAndDuration(
                        $latitude,
                        $longitude,
                        $address->lat,
                        $address->lng
                    );
                    if ($distance) {
                        $distanceInKm = $distance['distance'] / 1000;
                        $durationInMin = $distance['duration'];
                    }
                    $ratePerKm = (float) $this->settingRepo->getValue('delivery_fee_per_km');
                    return [
                        'success' => true,
                        'message' => __('message.Success'),
                        'data'    => (float) ($distanceInKm * $ratePerKm),
                    ];

                case 'area':
                    if (!$areaId)
                        return [
                            'success' => false,
                            'message' => __('message.Area Id Is Required'),
                        ];

                    $areas = $this->cityRepo->getCityWithAreas($address->city_id);
                    foreach ($areas->areas as $area) {
                        if ($area?->id == $areaId) {
                            return [
                                'success' => true,
                                'message' => __('message.Success'),
                                'data'    => (float) $area->price,
                            ];
                        }
                    }
                default:
                    return [
                        'success' => false,
                        'message' => __('message.Invalid Delivery Type'),
                    ];
            }
        } else
            return [
                'success' => true,
                'message' => __('message.Success'),
                'data'    => 0,
            ];
    }
}
