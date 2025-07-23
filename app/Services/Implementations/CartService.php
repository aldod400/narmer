<?php

namespace App\Services\Implementations;

use App\Helpers\PriceHelpers;
use App\Repository\Contracts\CartRepositoryInterface;
use App\Repository\Contracts\ProductRepositoryInterface;
use App\Services\Contracts\CartServiceInterface;
use Illuminate\Support\Facades\DB;

class CartService implements CartServiceInterface
{
    protected $cartRepo;
    protected $productRepo;
    public function __construct(
        CartRepositoryInterface $cartRepo,
        ProductRepositoryInterface $productRepo
    ) {
        $this->cartRepo = $cartRepo;
        $this->productRepo = $productRepo;
    }
    public function getCart()
    {
        $carts = $this->cartRepo->getUserCart(auth('api')->user()->id);

        foreach ($carts as $cart) {
            $cart->product->image =  optional($cart->product->images->first())->image;
            unset($cart->product->images);
        }

        $data = PriceHelpers::calcolatePriceForCart($carts);

        return ['items' => $carts, 'pricing' => $data];
    }
    public function create(array $data)
    {
        $carts = $this->cartRepo->getUserCart(auth('api')->user()->id);
        $productId = $data['product_id'];
        $productAttributeValueIds = isset($data['product_attribute_value_ids'])
            ? $data['product_attribute_value_ids'] : [];

        foreach ($carts as $cart) {
            if ($cart->product_id == $productId) {
                $existingIds = collect($cart->productAttributeValues)->pluck('id')->sort()->values()->toArray();
                $newIds = collect($productAttributeValueIds)->sort()->values()->toArray();
                if ($existingIds === $newIds) {
                    return [
                        'success' => false,
                        'message' => __('message.Product already in cart with selected options')
                    ];
                }
            }
        }
        $product = $this->productRepo->find($data['product_id']);
        if ($product->quantity < $data['quantity'])
            return [
                'success' => false,
                'message' => __('message.Product quantity is not enough')
            ];

        if (!isset($data['product_attribute_value_ids']) && $product->productAttributes->count() > 0)
            return [
                'success' => false,
                'message' => __('message.Attribute values is required')
            ];
        if (isset($data['product_attribute_value_ids'])) {
            $allExist = collect($data['product_attribute_value_ids'])->every(function ($attributeValueId) use ($product) {
                return $product->productAttributes->contains('id', $attributeValueId);
            });
            if (!$allExist)
                return [
                    'success' => false,
                    'message' => __('message.Attribute values is not valid to this Product')
                ];
        }
        $Cartdata = [
            'user_id' => auth('api')->user()->id,
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
        ];

        DB::beginTransaction();
        try {
            $cart = $this->cartRepo->create($Cartdata);
            if (isset($data['product_attribute_value_ids']))
                $cart->ProductAttributeValues()
                    ->sync($data['product_attribute_value_ids']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'success' => true,
            'message' => __('message.Success'),
            'data' => $cart,
        ];
    }
    public function update(int $id, int $quantity)
    {
        $cart = $this->cartRepo->find($id);
        if ($cart->user_id != auth('api')->user()->id)
            return [
                'success' => false,
                'message' => __('message.you are not authorized to update this cart')
            ];
        $product = $this->productRepo->find($cart->product_id);

        if ($product->quantity < $quantity)
            return [
                'success' => false,
                'message' => __('message.Product quantity is not enough')
            ];
        $cart = $this->cartRepo->update($id, $quantity);

        return [
            'success' => true,
            'message' => __('message.Success'),
            'data'    =>  $cart,
        ];
    }
    public function delete(int $id)
    {
        $cart = $this->cartRepo->find($id);
        if ($cart->user_id != auth('api')->user()->id)
            return [
                'success' => false,
                'message' => __('message.you are not authorized to update this cart')
            ];
        $this->cartRepo->delete($id);
        return [
            'success' => true,
            'message' => __('message.Success'),
        ];
    }
    public function getDeliveryFee(int $addressId) {}
}
