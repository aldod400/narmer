<?php

namespace App\Filament\Resources\DeliverymanResource\Pages;

use App\Filament\Resources\DeliverymanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryman extends EditRecord
{
    protected static string $resource = DeliverymanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
