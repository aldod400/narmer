<?php

namespace App\Services\Implementations;

use App\Helpers\PriceHelpers;
use App\Repository\Contracts\AddressRepositoryInterface;
use App\Repository\Contracts\AuthRepositoryInterface;
use App\Repository\Contracts\CartRepositoryInterface;
use App\Repository\Contracts\OrderRepositoryInterface;
use App\Repository\Contracts\PaymobPaymentRepositoryInterface;
use App\Services\Contracts\DeliveryFeeServiceInterface;
use App\Services\Contracts\FirebaseServiceInterface;
use App\Services\Contracts\OrderServiceInterface;
use App\Services\Contracts\PaymobServiceInterface;
use Illuminate\Support\Facades\DB;

class OrderService implements OrderServiceInterface
{
    protected $orderRepo;
    protected $deliveryFeeService;
    protected $cartRepo;
    protected $addressRepo;
    protected $paymobService;
    protected $paymentRepo;
    protected $firebaseService;
    protected $authRepo;

    public function __construct(
        OrderRepositoryInterface $orderRepo,
        DeliveryFeeServiceInterface $deliveryFeeService,
        CartRepositoryInterface $cartRepo,
        AddressRepositoryInterface $addressRepo,
        PaymobServiceInterface $paymobService,
        PaymobPaymentRepositoryInterface $paymentRepositor,
        FirebaseServiceInterface $firebaseService,
        AuthRepositoryInterface $authRepo
    ) {
        $this->orderRepo = $orderRepo;
        $this->deliveryFeeService = $deliveryFeeService;
        $this->cartRepo = $cartRepo;
        $this->addressRepo = $addressRepo;
        $this->paymobService = $paymobService;
        $this->paymentRepo = $paymentRepositor;
        $this->firebaseService = $firebaseService;
        $this->authRepo = $authRepo;
    }
    public function create(array $data)
    {
        $carts = $this->cartRepo->getUserCart($data['user_id']);

        if ($carts->isEmpty())
            return [
                'success' => false,
                'message' => __('message.Cart Is Empty'),
            ];

        $addresses = $this->addressRepo->getAddresses($data['user_id']);
        $addressFound = false;
        foreach ($addresses as $address) {
            if ($address->id == $data['address_id']) {
                $addressFound = true;
                break;
            }
        }

        if (!$addressFound)
            return [
                'success' => false,
                'message' => __('message.Address Not Found'),
            ];

        $subtotal = PriceHelpers::calcolatePriceForCart($carts);

        $devaliveryFee = $this->deliveryFeeService->calculateDeliveryFee($data['address_id'], $data['area_id']);

        if (!$devaliveryFee['success'])
            return [
                'success' => false,
                'message' => $devaliveryFee['message']
            ];

        $subtotal['total_price_after_discount'] += $devaliveryFee['data'];

        $order = DB::transaction(function () use ($carts, $data, $subtotal, $devaliveryFee) {
            $paymentData = null;
            $order = $this->orderRepo->create([
                'user_id' => $data['user_id'],
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'unpaid',
                'subtotal' => $subtotal['total_price'],
                'total' => $subtotal['total_price_after_discount'],
                'notes' => $data['notes'] ?? null,
                'order_type' => 'online',
                'coupon_id' => $data['coupon_id'] ?? null,
                'discount' => $subtotal['discount'],
                'delivery_fee' => $devaliveryFee['data'],
                'area_id' => $data['area_id'] ?? null,
                'deliveryman_id' => null,
                'created_by' => $data['user_id'],
                'address_id' => $data['address_id'],
            ]);
            foreach ($carts as $cart) {
                $orderDetail = $order->orderDetails()->create([
                    'product_id' => $cart->product_id,
                    'quantity' => $cart->quantity,
                    'price' => $cart->product->price,
                ]);

                if ($cart->productAttributeValues)
                    foreach ($cart->productAttributeValues as $attributeValue) {
                        $orderDetail->attributeValues()->create([
                            'attribute_value_id' => $attributeValue->attribute_value_id,
                        ]);
                    }
            }
            if ($order->payment_method == 'cash')
                $carts->each->delete();

            if ($order->payment_method != 'cash') {
                $paymentData = $this->paymobService->generatePaymentLink(
                    (string)$order->id,
                    $order->payment_method == 'visa' ? 'card' : 'wallet',
                    (float)$order->total,
                    [
                        'name' => $order->user->name,
                        'email' => $order->user->email,
                        'phone' => $order->user->phone
                    ],
                    $order->payment_method == 'visa' ? 'card' : 'wallet',
                    $data['wallet_number']
                );
            }

            if ($paymentData)
                $this->paymentRepo->create([
                    'user_id' => $order->user_id,
                    'amount' => $order->total,
                    'payment_method' => $order->payment_method,
                    'status' => 'pending',
                    'order_id' => $paymentData['order_id'],
                    'pending' => true,
                    'success' => false,
                    'my_order_id' => $order->id
                ]);
            return [
                'order' => $order,
                'paymentData' => $paymentData
            ];
        });

        $this->authRepo->getAdmins()->each(function ($admin) use ($order) {
            $this->firebaseService->sendNotification(
                __('message.New Order'),
                __('message.New Order Created') . ' #' . $order['order']->id,
                'user',
                $admin->fcm_token ?? '',
                false,
                $admin->id,
                null,
            );
        });


        return [
            'success' => true,
            'message' => __('message.Success'),
            'data' => [
                'order_id' => $order['order']->id,
                'link' => $order['paymentData']['payment_url'] ?? null,
            ],
        ];
    }
    public function getOrders(int $userId, int $perPage, ?string $status, ?string $paymentStatus, ?string $paymentMethod, ?string $search)
    {
        $orders = $this->orderRepo->getOrders($userId, $perPage, $status, $paymentStatus, $paymentMethod, $search);
        $orders->map(function ($order) {
            $order->orderDetails->map(function ($orderDetail) {
                $orderDetail->product->image = $orderDetail->product->images->first()?->image;
                unset($orderDetail->product->images);
            });
        });
        return [
            'success' => true,
            'message' => __('message.Success'),
            'data' => $orders
        ];
    }
    public function getOrderById(int $id)
    {
        $order = $this->orderRepo->getOrderById($id);
        return [
            'success' => true,
            'message' => __('message.Success'),
            'data' => $order
        ];
    }
    public function getDeliverymanOrders(int $deliverymanId, int $perPage, ?string $status = null, ?string $paymentStatus, ?string $paymentMethod, ?string $search)
    {
        $orders = $this->orderRepo->getDeliverymanOrders($deliverymanId, $perPage, $status, $paymentStatus, $paymentMethod, $search);
        $orders->map(function ($order) {
            $order->orderDetails->map(function ($orderDetail) {
                $orderDetail->product->image = $orderDetail->product->images->first()?->image;
                unset($orderDetail->product->images);
            });
        });

        return [
            'success' => true,
            'message' => __('message.Success'),
            'data' => $orders
        ];
    }
    public function updateOrderStatusByDeliveryman(int $orderId, string $status)
    {
        $order = $this->orderRepo->getOrderById($orderId);

        if ($order->deliveryman_id != auth('api')->user()->id)
            return [
                'success' => false,
                'message' => __('message.Unauthorized'),
            ];

        if ($order->status == 'delivered')
            return [
                'success' => false,
                'message' => __('message.Order Already Delivered'),
            ];

        if ($order->status == 'ready' && $status != 'on_delivery')
            return [
                'success' => false,
                'message' => __('message.Invalid Status Change'),
            ];

        if ($order->status == 'on_delivery' && $status != 'delivered')
            return [
                'success' => false,
                'message' => __('message.Invalid Status Change'),
            ];

        if (!in_array($status, ['on_delivery', 'delivered']))
            return [
                'success' => false,
                'message' => __('message.Invalid Status'),
            ];

        $this->orderRepo->updateOrderStatus($orderId, $status);

        return [
            'success' => true,
            'message' => __('message.Success')
        ];
    }
}
