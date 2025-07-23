<?php

namespace App\Filament\Resources\DeliverymanResource\Pages;

use App\Filament\Resources\DeliverymanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliverymen extends ListRecords
{
    protected static string $resource = DeliverymanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
