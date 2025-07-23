<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddressRequest;
use App\Services\Contracts\AddressServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AddressController extends Controller
{
    protected $addressService;
    public function __construct(AddressServiceInterface $addressService)
    {
        $this->addressService = $addressService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $addresses = $this->addressService
            ->getAddresses(auth('api')->user()->id);

        return Response::api(__('message.Success'), 200, true, null, $addresses);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AddressRequest $request)
    {
        $address = $this->addressService->create([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'is_default' => $request->is_default,
            'city_id' => $request->city_id,
            'user_id' => auth('api')->user()->id,
        ]);
        return Response::api(__('message.Success'), 200, true, null, $address);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AddressRequest $request, string $id)
    {
        $address = $this->addressService->update(
            $id,
            [
                'name' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'lat' => $request->lat,
                'lng' => $request->lng,
                'is_default' => $request->is_default,
                'city_id' => $request->city_id,
                'user_id' => auth('api')->user()->id,
            ]
        );
        return Response::api(__('message.Success'), 200, true, null, $address);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->addressService->delete($id);
        return Response::api(__('message.Success'), 200, true, null);
    }
}
