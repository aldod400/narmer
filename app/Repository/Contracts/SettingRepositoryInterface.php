<?php

namespace App\Repository\Contracts;

interface SettingRepositoryInterface
{
    public function getDeliverymanValue();
    public function getAll();
    public function getValue(string $key);
}
