<?php

namespace App\Services\Contracts;

interface NotificationServiceInterface
{
    public function getNotifications(?int $userId);
}
