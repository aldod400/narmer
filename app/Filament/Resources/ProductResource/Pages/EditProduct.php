<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditProduct extends EditRecord
{
    use \Filament\Actions\Concerns\CanNotify;
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        if ($this->record->images()->count() === 0) {
            Notification::make()
                ->title(__('message.Product must have at least one image.'))
                ->danger()
                ->send();

            $this->halt(); // تمنع الحفظ
        }
    }
}
