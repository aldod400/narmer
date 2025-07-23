<?php

namespace App\Repository\Contracts;

interface AddressRepositoryInterface
{
    public function find(int $id);
    public function create(array $data);
    public function getDefaultAddress(int $userId);
    public function getAddresses(int $userId);
    public function update(int $id, array $data);
    public function delete(int $id);
}
