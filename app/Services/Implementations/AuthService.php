<?php

namespace App\Services\Implementations;

use App\Helpers\ImageHelpers;
use App\Repository\Contracts\AuthRepositoryInterface;
use App\Services\Contracts\AuthServiceInterface;
use App\Strategies\Contracts\Login\LoginStrategyInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    protected $authRepository;
    protected $loginStrategies;

    public function __construct(
        AuthRepositoryInterface $authRepository,
        LoginStrategyInterface ...$loginStrategies
    ) {
        $this->authRepository = $authRepository;
        $this->loginStrategies = $loginStrategies;
    }

    public function login(string $identifier, string $password, ?string $fcmToken)
    {
        $strategy = collect($this->loginStrategies)
            ->first(function ($strategy) use ($identifier) {
                return $strategy->canHandle($identifier);
            });

        if (!$strategy)
            return [
                'success' => false,
                'message' => __('message.No strategy found for') . ' ' . $identifier,
            ];

        $result = $strategy->login($identifier, $password);

        if (!$result['success'])
            return $result;

        $token = auth('api')->login($result['user']) ?? '';

        if ($fcmToken)
            $this->updateFcmToken($fcmToken);

        return [
            'success' => true,
            'user' => $result['user'],
            'token' => $token
        ];
    }

    public function register(array $data)
    {
        if ($data['image'])
            $data['image'] = ImageHelpers::addImage($data['image'], 'users');

        $data['image'] = $data['image'] ?? 'assets/img/default.png';

        $data['password'] = Hash::make($data['password']);

        $user = $this->authRepository->createUser($data);

        $token = auth('api')->login($user) ?? '';

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function profile()
    {
        $user = auth('api')->user();
        return $user;
    }

    public function updateProfile(array $data)
    {
        $user = auth('api')->user();
        if ($data['image']) {
            if (File::exists($user->image)) {
                File::delete($user->image);
            }
            $data['image'] = ImageHelpers::addImage($data['image'], 'users');
        }

        $data['image'] = $data['image'] ?? $user->image;


        $data['email'] = $data['email'] ?? $user->email;
        $data['phone'] = $data['phone'] ?? $user->phone;

        if ($data['password'])
            $data['password'] = Hash::make($data['password']);
        else
            $data['password'] = $user->password;

        if ($data['fcm_token'])
            $this->updateFcmToken($data['fcm_token']);

        return $this->authRepository->updateUserById($user->id, $data);
    }

    public function updateFcmToken(string $fcmToken)
    {
        $userId = auth('api')->user()->id;

        $data = ['fcm_token' => $fcmToken];

        return $this->authRepository->updateUserById($userId, $data);
    }
    public function deleteProfile()
    {
        $user = auth('api')->user();
        if (File::exists($user->image)) {
            File::delete($user->image);
        }
        $this->authRepository->deleteUserById($user->id);
        return true;
    }
    public function saveFcmTokenToAdmin(string $fcmToken, int $userId)
    {
        $data = $this->authRepository->updateUserById($userId, [
            'fcm_token' => $fcmToken
        ]);

        return [
            'success' => true,
            'message' => __('message.FCM token updated successfully'),
            'data' => $data
        ];
    }
}
