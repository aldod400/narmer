<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateNotification extends CreateRecord
{
    protected static string $resource = NotificationResource::class;

    protected function afterCreate(): void
    {
        $notificationRepo = app(\App\Repository\Contracts\NotificationRepositoryInterface::class);
        $notification = new \App\Services\Implementations\FirebaseService($notificationRepo);

        $record = $this->record;

        $notification->sendNotification(
            $record->title,
            $record->body,
            'topic',
            $record->to,
            false,
            null,
            $record->image,
            null
        );
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
