<?php

namespace App\Http\Controllers\Api\Deliveryman;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\FilterOrderRequest;
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
        $result = $this->orderService->getDeliverymanOrders(
            auth('api')->id(),
            $request->per_page ?? 10,
            $request->status,
            $request->payment_status,
            $request->payment_method,
            $request->search
        );
        if (!$result['success'])
            return Response::api($result['message'], 400, false, 400);

        return Response::api($result['message'], 200, true, 200, $result['data']);
    }
    public function changeStatus(int $id, Request $request)
    {
        $validator = validator($request->all(), [
            'status' => 'required|in:on_delivery,delivered'
        ]);

        if ($validator->fails()) {
            return Response::api($validator->errors()->first(), 400, false, 400);
        }
        $result = $this->orderService->updateOrderStatusByDeliveryman($id, $request->status);

        if (!$result['success'])
            return Response::api($result['message'], 400, false, 400);

        return Response::api($result['message'], 200, true, 200);
    }
}
