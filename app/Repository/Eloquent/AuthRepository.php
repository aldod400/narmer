<?php

namespace App\Repository\Eloquent;

use App\Models\User;
use App\Repository\Contracts\AuthRepositoryInterface;

class AuthRepository implements AuthRepositoryInterface
{
    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function findByPhone(string $phone)
    {
        return User::where('phone', $phone)->first();
    }


    public function createUser(array $data)
    {
        return User::create($data);
    }

    public function updateUserById(string $id, array $data)
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user;
    }
    public function getAdmins()
    {
        return User::where('user_type', 'admin')->get();
    }
    public function deleteUserById(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return $user;
    }
}
