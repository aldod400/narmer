<?php

namespace App\Filament\Resources\DeliveryFeeResource\Pages;

use App\Filament\Resources\DeliveryFeeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveryFee extends CreateRecord
{
    protected static string $resource = DeliveryFeeResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
