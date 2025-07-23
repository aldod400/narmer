<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\NotificationServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    protected $notificationService;
    public function __construct(NotificationServiceInterface $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function index()
    {
        $notifications = $this->notificationService->getNotifications(auth('api')->user()?->id);

        return Response::api(__('message.Success'), 200, true, null, $notifications);
    }
}
