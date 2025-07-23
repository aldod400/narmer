<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreWishlistRequest;
use App\Services\Contracts\WishlistServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WishlistController extends Controller
{
    protected $wishlistService;
    public function __construct(WishlistServiceInterface $wishlistService)
    {
        $this->wishlistService = $wishlistService;
    }
    public function index()
    {
        $wishlists = $this->wishlistService
            ->getAllByUserId(auth('api')->user()->id);

        return Response::api(__('message.Success'), 200, true, null, $wishlists);
    }
    public function store(StoreWishlistRequest $request)
    {
        $result = $this->wishlistService->add(
            auth('api')->user()->id,
            $request->product_id
        );

        if (!$result['success'])
            return Response::api($result['message'], 400, false, 400);

        return Response::api(__('message.Success'), 200, true, null, $result['data']);
    }
    public function destroy(int $productId)
    {
        $result = $this->wishlistService->remove(auth('api')->user()->id, $productId);

        if (!$result['success'])
            return Response::api($result['message'], 400, false, 400);

        return Response::api(__('message.Success'), 200, true, null);
    }
}
