<?php

namespace App\Strategies\Implementations\Login;

use App\Helpers\HandlesPasswordLogin;
use App\Repository\Contracts\AuthRepositoryInterface;
use App\Repository\Contracts\SettingRepositoryInterface;
use App\Strategies\Contracts\Login\LoginStrategyInterface;
use Illuminate\Support\Facades\Hash;

class EmailLoginStrategy implements LoginStrategyInterface
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
    public function canHandle(string $identifier)
    {
        return filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function login(string $identifier, string $password)
    {
        $user = $this->authRepository->findByEmail($identifier);

        return $this->performPasswordCheck($user, $password, $this->settingRepository);
    }
}
