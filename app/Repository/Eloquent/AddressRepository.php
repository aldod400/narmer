<?php

namespace App\Repository\Eloquent;

use App\Models\Address;
use App\Models\UserAddress;
use App\Repository\Contracts\AddressRepositoryInterface;

class AddressRepository implements AddressRepositoryInterface
{
    public function find(int $id)
    {
        return Address::with(['city' => function ($query) {
            $query->select('id', app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name');
        }])->findOrFail($id);
    }
    public function create(array $data)
    {
        return Address::with(['city' => function ($query) {
            $query->select('id', app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name');
        }])->create($data);
    }
    public function getDefaultAddress(int $userId)
    {
        return Address::where('user_id', $userId)
            ->where('is_default', true)->first();
    }
    public function getAddresses(int $userId)
    {
        return Address::with(['city' => function ($query) {
            $query->select('id', app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name');
        }])->where('user_id', $userId)->get();
    }
    public function update(int $id, array $data)
    {
        $address = Address::with(['city' => function ($query) {
            $query->select('id', app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name');
        }])->findOrFail($id);
        $address->update($data);

        return $address;
    }
    public function delete(int $id)
    {
        $address = Address::findOrFail($id);
        return $address->delete();
    }
}
