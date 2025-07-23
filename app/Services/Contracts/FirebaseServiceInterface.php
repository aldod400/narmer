<?php

namespace App\Services\Contracts;

interface FirebaseServiceInterface
{
    public function sendNotification($title, $body, $type, $to, $save = false, $user_id = null, $image = null, $data = null);
}
