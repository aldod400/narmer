<?php

namespace App\Services\Implementations;

use App\Enums\ProductStatus;
use App\Repository\Contracts\WishlistRepositoryInterface;
use App\Services\Contracts\WishlistServiceInterface;

class WishlistService implements WishlistServiceInterface
{
    protected $wishlistRepo;

    public function __construct(WishlistRepositoryInterface $wishlistRepo)
    {
        $this->wishlistRepo = $wishlistRepo;
    }
    public function getAllByUserId(int $userId)
    {
        $wishlists = $this->wishlistRepo->getAllByUserId($userId);

        $wishlists = $wishlists
            ->filter(function ($wishlist) {
                return $wishlist->product && $wishlist->product->status == ProductStatus::ACTIVE;
            })->map(function ($wishlist) {
                $wishlist->product->image = optional($wishlist->product->images->first())->image;
                unset($wishlist->product->images);

                return $wishlist;
            })->values();

        return $wishlists;
    }
    public function add(int $userId, int $productId)
    {
        $wishlists = $this->wishlistRepo->getAllByUserId($userId);
        if ($wishlists->count() > 0 && $wishlists->contains('product_id', $productId))
            return ['success' => false, 'message' => __('message.Product already in wish list')];

        $wishlist = $this->wishlistRepo->create($userId, $productId);

        return [
            'success' => true,
            'message' => __('message.Success'),
            'data'    => $wishlist
        ];
    }
    public function remove(int $userId, int $productId)
    {
        $wishlist = $this->wishlistRepo->find($userId, $productId);
        if (!$wishlist)
            return ['success' => false, 'message' => __('message.Product not found in wish list')];
        if ($wishlist->user_id != auth('api')->user()->id)
            return ['success' => false, 'message' => __('message.you are not authorized to delete this wish list')];

        $wishlist = $this->wishlistRepo->delete($userId, $productId);
        return [
            'success' => true,
            'message' => __('message.Success')
        ];
    }
}
