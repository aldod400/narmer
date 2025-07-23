<?php

namespace App\Strategies\Implementations\Login;

use App\Repository\Contracts\AuthRepositoryInterface;
use App\Services\PasswordLoginServiceHelper;
use App\Strategies\Contracts\Login\LoginStrategyInterface;
use App\Helpers\HandlesPasswordLogin;
use App\Repository\Contracts\SettingRepositoryInterface;

class PhoneLoginStrategy implements LoginStrategyInterface
{
    use HandlesPasswordLogin;
    protected $authRepository;
    protected $settingRepository;
    public function __construct(
        AuthRepositoryInterface $authRepository,
        SettingRepositoryInterface $settingRepository
    ) {
        $this->authRepository = $authRepository;
        $this->settingRepository = $settingRepository;
    }
    public function canHandle(string $identifier): bool
    {
        return preg_match('/^\d+$/', $identifier);
    }

    public function login(string $identifier, string $password)
    {
        $user = $this->authRepository->findByPhone($identifier);

        return $this->performPasswordCheck($user, $password, $this->settingRepository);
    }
}
