<?php

namespace App\Strategies\Contracts\Login;

interface LoginStrategyInterface
{
    public function canHandle(string $identifier);
    public function login(string $identifier, string $password);
}
