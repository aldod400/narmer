<?php

namespace App\Helpers;

use App\Enums\UserType;
use App\Repository\Contracts\SettingRepositoryInterface;
use Illuminate\Support\Facades\Hash;

trait HandlesPasswordLogin
{
    protected function performPasswordCheck($user, string $password, SettingRepositoryInterface $settingRepository): array
    {
        if (!$user) {
            return ['success' => false, 'message' => __('message.Invalid Email or Phone')];
        }

        if (!Hash::check($password, $user->password)) {
            return ['success' => false, 'message' => __('message.Invalid Password')];
        }
        if ($user->user_type == UserType::DELIVERYMAN) {
            $enabled = $settingRepository->getDeliverymanValue();
            if ($enabled == '0')
                return ['success' => false, 'message' => __('message.Deliveryman is disabled')];
        }

        return ['success' => true, 'user' => $user];
    }
}
