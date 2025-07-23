<?php

namespace App\Repository\Contracts;

interface AuthRepositoryInterface
{
    public function findByEmail(string $email);
    public function findByPhone(string $phone);

    public function createUser(array $data);
    public function updateUserById(string $id, array $data);
    public function getAdmins();
    public function deleteUserById(string $id);
}
