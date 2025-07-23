<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\FilterOrderRequest;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Services\Contracts\OrderServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    protected $orderService;
    public function __construct(OrderServiceInterface $orderService)
    {
        $this->orderService = $orderService;
    }
    public function index(FilterOrderRequest $request)
    {
        $result = $this->orderService->getOrders(
            auth('api')->user()->id,
            $request->per_page ?? 10,
            $request->status ?? null,
            $request->payment_status ?? null,
            $request->payment_method ?? null,
            $request->search ?? null
        );

        if (!$result['success'])
            return Response::api($result['message'], 400, false, 400);

        return Response::api($result['message'], 200, true, 200, $result['data']);
    }
    public function store(StoreOrderRequest $request)
    {
        $result = $this->orderService->create([
            'address_id' => $request->address_id,
            'area_id' => $request->area_id,
            'user_id' => auth('api')->user()->id,
            'coupon_id' => $request->coupon_id,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
            'wallet_number' => $request->wallet_number ?? null,
        ]);

        if (!$result['success'])
            return Response::api($result['message'], 400, false, 400);

        return Response::api($result['message'], 200, true, 200, $result['data']);
    }
    public function show(int $id)
    {
        $result = $this->orderService->getOrderById($id);

        if (!$result['success'])
            return Response::api($result['message'], 400, false, 400);

        return Response::api($result['message'], 200, true, 200, $result['data']);
    }
}
