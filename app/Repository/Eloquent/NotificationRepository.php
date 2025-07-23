<?php

namespace App\Repository\Eloquent;

use App\Models\Notification;
use App\Repository\Contracts\NotificationRepositoryInterface;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function getNotificationsByUserId($userId)
    {
        return Notification::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhere(function ($query) {
                    $query->where('to', 'user')
                        ->where('type', 'topic');
                })->orWhere(function ($query) {
                    $query->where('to', 'all')
                        ->where('type', 'topic');
                });
        })->orderByDesc('created_at')->get();
    }
    public function create(array $data)
    {
        return Notification::create($data);
    }
    public function getNotifications()
    {
        return Notification::where(function ($query) {
            $query->where('type', 'topic')
                ->where('to', 'user');
        })->orWhere(function ($query) {
            $query->where('type', 'topic')
                ->where('to', 'all');
        })
            ->orderByDesc('created_at')->get();
    }
}
