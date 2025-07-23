<?php

namespace App\Services\Implementations;

use App\Repository\Contracts\AddressRepositoryInterface;
use App\Services\Contracts\AddressServiceInterface;

class AddressService implements AddressServiceInterface
{
    protected $addressRepo;
    public function __construct(AddressRepositoryInterface $addressRepo)
    {
        $this->addressRepo = $addressRepo;
    }
    public function find(int $id)
    {
        return $this->addressRepo->find($id);
    }
    public function create(array $data)
    {
        if ($data['is_default'] == true) {
            $address = $this->addressRepo->getDefaultAddress($data['user_id']);
            if ($address)
                $this->addressRepo
                    ->update($address->id, ['is_default' => false]);
        }
        $addresses = $this->addressRepo->getAddresses($data['user_id']);
        if (count($addresses) == 0)
            $data['is_default'] = true;

        return $this->addressRepo->create($data);
    }
    public function getAddresses(int $userId)
    {
        return $this->addressRepo->getAddresses($userId);
    }
    public function update(int $id, array $data)
    {
        if ($data['is_default'] == true) {
            $address = $this->addressRepo->getDefaultAddress($data['user_id']);
            if ($address)
                $this->addressRepo
                    ->update($address->id, ['is_default' => false]);
        }
        return $this->addressRepo->update($id, $data);
    }
    public function delete(int $id)
    {
        $address = $this->addressRepo->find($id);
        if ($address->is_default == true) {
            $addresses = $this->addressRepo->getAddresses($address->user_id);
            if (count($addresses) > 1) {
                $this->addressRepo->update($addresses[0]->id, ['is_default' => true]);
            }
        }
        return $this->addressRepo->delete($id);
    }
}
