<?php

namespace App\Repository\Eloquent;

use App\Models\Order;
use App\Repository\Contracts\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function getOrders(int $userId, int $perPage, ?string $status, ?string $paymentStatus, ?string $paymentMethod, ?string $search)
    {
        $query = Order::query();
        $query->where('user_id', $userId);
        if ($status)
            $query->where('status', $status);

        if ($paymentStatus)
            $query->where('payment_status', $paymentStatus);

        if ($paymentMethod)
            $query->where('payment_method', 'like', "%{$search}%");

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('subtotal', 'like', "%{$search}%")
                    ->orWhere('total', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('order_type', 'like', "%{$search}%")
                    ->orWhere('discount', 'like', "%{$search}%")
                    ->orWhere('delivery_fee', 'like', "%{$search}%")
                    ->orWhereRelation('coupon', 'code', "%{$search}%")
                    ->orWhereRelation('address', 'name', "%{$search}%");
            });
        }
        $orders = $query->with(['coupon', 'address.city' => function ($query) {
            $query->select(
                'id',
                app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name',
                'created_at',
                'updated_at'
            );
        }, 'area', 'orderDetails.product' => function ($query) {
            $query->select(
                'id',
                app()->getLocale() === 'ar' ? 'name_ar as name' : 'name_en as name',
                'slug',
                'description',
                'price',
                'discount_price',
                'quantity',
                'status',
                'category_id',
                'brand_id',
                'created_at',
                'updated_at',
            );
        }, 'orderDetails.product.images' => function ($query) {
            $query->take(1);
        }])->paginate($perPage);

        return $orders;
    }
    public function create(array $data)
    {
        return Order::create($data);
    }
    public function update(int $id, array $data)
    {
        return Order::findOrFail($id)->update($data);
    }
    public function getOrderById(int $id)
    {
        return Order::with(
            [
                'address',
                'area',
                'user',
                'deliveryman',
                'coupon',
                'orderDetails',
                'orderDetails.product',
                'orderDetails.attributeValues.attributeValue',
                'orderDetails.attributeValues.attributeValue.attribute',
                'creator'
            ]
        )->findOrFail($id);
    }
    public function getDeliverymanOrders(int $deliverymanId, int $perPage, ?string $status = null, ?string $paymentStatus, ?string $paymentMethod, ?string $search)
    {
        $query = Order::query();
        $query->where('deliveryman_id', $deliverymanId);
        if ($status) {
            if ($status == 'completed') {
                $query->whereIn('status', ['delivered', 'canceled']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($paymentStatus)
            $query->where('payment_status', $paymentStatus);

        if ($paymentMethod)
            $query->where('payment_method', 'like', "%{$search}%");

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('subtotal', 'like', "%{$search}%")
                    ->orWhere('total', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('order_type', 'like', "%{$search}%")
                    ->orWhere('discount', 'like', "%{$search}%")
                    ->orWhere('delivery_fee', 'like', "%{$search}%")
                    ->orWhereRelation('coupon', 'code', "%{$search}%")
                    ->orWhereRelation('address', 'name', "%{$search}%")
                    ->orWhereRelation('user', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }
        return $query->with(['orderDetails.product' => function ($query) {
            $query->select(
                'id',
                app()->getLocale() === 'ar' ? 'name_ar as name' : 'name_en as name',
                'slug',
                'description',
                'price',
                'discount_price',
                'quantity',
                'status',
                'category_id',
                'brand_id',
                'created_at',
                'updated_at',
            );
        }, 'user', 'coupon', 'address.city' => function ($query) {
            $query->select(
                'id',
                app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name',
                'created_at',
                'updated_at'
            );
        }, 'area' => function ($query) {
            $query->select(
                'id',
                'city_id',
                app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name',
                'price',
                'created_at',
                'updated_at'
            );
        }])->orderByDesc('id')->paginate($perPage);
    }
    public function updateOrderStatus(int $orderId, string $status)
    {
        return Order::findOrFail($orderId)->update(['status' => $status]);
    }
}
