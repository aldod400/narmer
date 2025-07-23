<?php

namespace App\Services\Contracts;

interface AddressServiceInterface
{
    public function find(int $id);
    public function create(array $data);
    public function getAddresses(int $userId);
    public function update(int $id, array $data);
    public function delete(int $id);
}
