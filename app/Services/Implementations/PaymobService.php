<?php

namespace App\Services\Implementations;

use App\Repository\Contracts\CartRepositoryInterface;
use App\Repository\Contracts\OrderRepositoryInterface;
use App\Repository\Contracts\PaymobPaymentRepositoryInterface;
use App\Services\Contracts\PaymobServiceInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymobService implements PaymobServiceInterface
{
    protected $apiKey;
    protected $integrationId;
    protected $hmacSecret;
    protected $walletIntegrationId;
    protected $iframeId;
    protected $baseUrl = 'https://accept.paymob.com/api';
    protected $client;
    protected $paymobPaymentRepo;
    protected $orderRepo;
    protected $cartRepo;

    /**
     * PaymobService constructor.
     * 
     * @param string|null $apiKey Paymob API key
     * @param string|null $integrationId Paymob Integration ID (Card payments)
     * @param string|null $hmacSecret Paymob HMAC secret
     * @param string|null $walletIntegrationId Paymob Wallet Integration ID (Vodafone Cash, etc.)
     * @param string|null $iframeId Paymob iFrame ID
     */
    public function __construct(
        PaymobPaymentRepositoryInterface $paymobPaymentRepo,
        OrderRepositoryInterface $orderRepo,
        CartRepositoryInterface $cartRepo
    ) {
        $this->paymobPaymentRepo = $paymobPaymentRepo;
        $this->orderRepo = $orderRepo;
        $this->cartRepo = $cartRepo;
        $this->apiKey = env('PAYMOB_API_KEY');
        $this->integrationId = env('PAYMOB_INTEGRATION_ID');
        $this->hmacSecret = env('PAYMOB_HMAC_SECRET');
        $this->walletIntegrationId = env('PAYMOB_WALLET_INTEGRATION_ID');
        $this->iframeId = env('PAYMOB_IFRAME_ID');

        $this->client = new Client();
    }

    /**
     * Generate a payment link with order ID
     * 
     * @param string $eventName Name of the event
     * @param string $ticketName Name of the ticket
     * @param float $price Price in EGP
     * @param array $customerInfo Customer information (optional)
     * @param string $paymentMethod Payment method ('card' or 'wallet')
     * @param string|null $walletNumber Phone number for wallet payment (required for wallet payments)
     * @return array|null Payment details (payment_url and order_id) or null on failure
     */
    public function generatePaymentLink(
        string $title,
        string $paid_type,
        float $price,
        array $customerInfo = [],
        string $paymentMethod = 'card',
        ?string $walletNumber = null
    ) {
        try {
            // Step 1: Authentication
            $authToken = $this->authenticate();
            if (!$authToken) {
                return null;
            }

            // Step 2: Create Order
            $orderId = $this->createOrder($authToken, $title, $paid_type, $price);
            if (!$orderId) {
                return null;
            }
            // Step 3: Create Payment Key
            $paymentKey = $this->createPaymentKey($authToken, $orderId, $price, $customerInfo, $paymentMethod);
            if (!$paymentKey) {
                return null;
            }

            // Step 4: Generate Payment URL or process wallet payment
            $paymentUrl = null;
            $transactionId = null;

            if ($paymentMethod === 'wallet') {
                if (!$walletNumber) {
                    Log::error('Wallet payment requires a wallet number');
                    return null;
                }
                $result = $this->processWalletPayment($paymentKey, $walletNumber);
                if (!$result) {
                    return null;
                }

                $paymentUrl = $result['payment_url'] ?? null;
                $transactionId = $result['transaction_id'] ?? null;
            } else {
                $paymentUrl = $this->generatePaymentUrl($paymentKey);
            }

            if (!$paymentUrl) {
                return null;
            }

            // Return both payment URL and order ID
            return [
                'payment_url' => $paymentUrl,
                'order_id' => $orderId,
                'transaction_id' => $transactionId
            ];
        } catch (\Exception $e) {
            Log::error('Paymob payment link generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Authenticate with Paymob API
     * 
     * @return string|null Authentication token or null on failure
     */
    protected function authenticate(): ?string
    {
        try {
            $response = $this->client->post($this->baseUrl . '/auth/tokens', [
                'json' => [
                    'api_key' => $this->apiKey
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['token'] ?? null;
        } catch (\Exception $e) {
            Log::error('Paymob authentication failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create an order in Paymob
     * 
     * @param string $authToken Authentication token
     * @param string $eventName Name of the event
     * @param string $ticketName Name of the ticket
     * @param float $price Price in EGP
     * @return int|null Order ID or null on failure
     */
    protected function createOrder(string $authToken, string $title, string $paid_type, float $price): ?int
    {
        try {
            $amountCents = (int) round($price * 100);
            $response = $this->client->post($this->baseUrl . '/ecommerce/orders', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $authToken
                ],
                'json' => [
                    'auth_token' => $authToken,
                    'delivery_needed' => false,
                    'amount_cents' => $amountCents,
                    'currency' => 'EGP',
                    'items' => [
                        [
                            'name' => $title,
                            'amount_cents' => $amountCents,
                            'description' => "$title - $paid_type",
                            'quantity' => 1
                        ]
                    ]
                ]
            ]);
            $data = json_decode($response->getBody(), true);
            return $data['id'] ?? null;
        } catch (\Exception $e) {
            Log::error('Paymob order creation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a payment key
     * 
     * @param string $authToken Authentication token
     * @param int $orderId Order ID
     * @param float $price Price in EGP
     * @param array $customerInfo Customer information
     * @param string $paymentMethod Payment method ('card' or 'wallet')
     * @return string|null Payment key or null on failure
     */
    protected function createPaymentKey(string $authToken, int $orderId, float $price, array $customerInfo, string $paymentMethod = 'card'): ?string
    {
        try {
            // Default customer info if not provided
            $defaultCustomer = [
                'first_name' => $customerInfo['first_name'] ?? 'Not',
                'last_name' => $customerInfo['last_name'] ?? 'Provided',
                'email' => $customerInfo['email'] ?? 'customer@example.com',
                'phone_number' => $customerInfo['phone_number'] ?? '00000000000',
            ];

            $customer = array_merge($defaultCustomer, array_filter($customerInfo));

            // Select appropriate integration ID based on payment method
            $integrationId = $paymentMethod === 'wallet'
                ? $this->walletIntegrationId
                : $this->integrationId;

            $response = $this->client->post($this->baseUrl . '/acceptance/payment_keys', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $authToken
                ],
                'json' => [
                    'auth_token' => $authToken,
                    'amount_cents' => (int) round($price * 100), // Convert to cents
                    'expiration' => 3600, // 1 hour
                    'order_id' => $orderId,
                    'billing_data' => [
                        'first_name' => $customer['first_name'],
                        'last_name' => $customer['last_name'],
                        'email' => $customer['email'],
                        'phone_number' => $customer['phone_number'],
                        'apartment' => 'NA',
                        'floor' => 'NA',
                        'street' => 'NA',
                        'building' => 'NA',
                        'shipping_method' => 'NA',
                        'postal_code' => 'NA',
                        'city' => 'NA',
                        'country' => 'NA',
                        'state' => 'NA'
                    ],
                    'currency' => 'EGP',
                    'integration_id' => $integrationId
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['token'] ?? null;
        } catch (\Exception $e) {
            Log::error('Paymob payment key creation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate payment URL from payment key
     * 
     * @param string $paymentKey Payment key
     * @return string Payment URL
     */
    protected function generatePaymentUrl(string $paymentKey): string
    {
        return "https://accept.paymob.com/api/acceptance/iframes/" . $this->iframeId . "?payment_token=" . $paymentKey;
    }

    /**
     * Process wallet payment
     * 
     * @param string $paymentKey Payment key
     * @param string $walletNumber Mobile number for wallet payment
     * @return array|null Payment details or null on failure
     */
    protected function processWalletPayment(string $paymentKey, string $walletNumber): ?array
    {
        try {
            $walletNumber = preg_replace('/[^0-9]/', '', $walletNumber);

            if (substr($walletNumber, 0, 1) === '0') {
                $walletNumber = '2' . substr($walletNumber, 1); // إضافة 2 (كود مصر) بدلاً من 0
            }

            $response = $this->client->post($this->baseUrl . '/acceptance/payments/pay', [
                'json' => [
                    'source' => [
                        'identifier' => $walletNumber,
                        'subtype' => 'WALLET'
                    ],
                    'payment_token' => $paymentKey
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            Log::info('Paymob wallet payment response: ' . json_encode($data));

            $result = [
                'payment_url' => null,
                'transaction_id' => $data['id'] ?? null
            ];

            if (isset($data['pending']) && $data['pending'] === true) {
                if (isset($data['redirect_url'])) {
                    $result['payment_url'] = $data['redirect_url'];
                } elseif (isset($data['id'])) {
                    $result['payment_url'] = "https://accept.paymob.com/api/acceptance/transactions/" . $data['id'];
                }
            }

            if (isset($data['error_occured']) && $data['error_occured'] === true) {
                $errorMsg = $data['message'] ?? 'خطأ غير معروف في المعاملة';
                Log::error('Paymob wallet payment error: ' . $errorMsg);
                return null;
            }

            if (isset($data['id']) && !$result['payment_url']) {
                $result['payment_url'] = "https://accept.paymob.com/api/acceptance/transactions/" . $data['id'];
            }

            if (!$result['payment_url']) {
                Log::error('Paymob wallet payment failed: No redirect or transaction ID');
                return null;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Paymob wallet payment exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate Paymob webhook
     * 
     * @param array $data Webhook data
     * @return bool Whether the webhook is valid
     */
    public function validateWebhook(array $data): bool
    {
        if (empty($this->hmacSecret) || empty($data['hmac'])) {
            return false;
        }
        $receivedHmac = $data['hmac'];


        $obj = $data['obj'] ?? null;

        $data =
            $obj['amount_cents'] .
            $obj['created_at'] .
            $obj['currency'] .
            ($obj['error_occured'] ? 'true' : 'false') .
            ($obj['has_parent_transaction'] ? 'true' : 'false') .
            $obj['id'] .
            $obj['integration_id'] .
            ($obj['is_3d_secure'] ? 'true' : 'false') .
            ($obj['is_auth'] ? 'true' : 'false') .
            ($obj['is_capture'] ? 'true' : 'false') .
            ($obj['is_refunded'] ? 'true' : 'false') .
            ($obj['is_standalone_payment'] ? 'true' : 'false') .
            ($obj['is_voided'] ? 'true' : 'false') .
            $obj['order']['id'] .
            $obj['owner'] .
            ($obj['pending'] ? 'true' : 'false') .
            $obj['source_data']['pan'] .
            $obj['source_data']['sub_type'] .
            $obj['source_data']['type'] .
            ($obj['success'] ? 'true' : 'false');


        $calculatedHmac = hash_hmac('sha512', $data, $this->hmacSecret);


        if ($receivedHmac !== $calculatedHmac) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check payment status
     * 
     * @param string $orderId The Paymob order ID
     * @return array|null Payment status details or null on failure
     */
    public function checkPaymentStatus(string $orderId): ?array
    {
        try {
            $authToken = $this->authenticate();
            if (!$authToken) {
                return null;
            }

            $response = $this->client->get($this->baseUrl . '/ecommerce/orders/' . $orderId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $authToken
                ]
            ]);

            $orderData = json_decode($response->getBody(), true);

            if (!empty($orderData['transactions'])) {
                $latestTransaction = end($orderData['transactions']);
                $transactionId = $latestTransaction['id'] ?? null;

                if ($transactionId) {
                    $transactionResponse = $this->client->get($this->baseUrl . '/acceptance/transactions/' . $transactionId, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $authToken
                        ]
                    ]);

                    $transactionData = json_decode($transactionResponse->getBody(), true);

                    return [
                        'order_data' => $orderData,
                        'transaction_data' => $transactionData,
                        'success' => ($transactionData['success'] ?? false),
                        'is_refunded' => ($transactionData['is_refunded'] ?? false),
                        'is_void' => ($transactionData['is_void'] ?? false),
                        'payment_status' => $this->getPaymentStatusText($transactionData)
                    ];
                }
            }

            return [
                'order_data' => $orderData,
                'success' => false,
                'payment_status' => 'pending'
            ];
        } catch (\Exception $e) {
            Log::error('Paymob payment status check failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get payment status text from transaction data
     * 
     * @param array $transactionData Transaction data
     * @return string Payment status text
     */
    protected function getPaymentStatusText(array $transactionData): string
    {
        if (isset($transactionData['is_void']) && $transactionData['is_void']) {
            return 'voided';
        }

        if (isset($transactionData['is_refunded']) && $transactionData['is_refunded']) {
            return 'refunded';
        }

        if (isset($transactionData['success']) && $transactionData['success']) {
            return 'success';
        }

        if (isset($transactionData['pending']) && $transactionData['pending']) {
            return 'pending';
        }

        if (isset($transactionData['error_occured']) && $transactionData['error_occured']) {
            return 'error';
        }

        return 'unknown';
    }

    /**
     * Get transaction by order ID
     * 
     * @param string $orderId The Paymob order ID
     * @return array|null Transaction data or null on failure
     */
    public function getTransactionByOrderId(string $orderId): ?array
    {
        try {
            $authToken = $this->authenticate();
            if (!$authToken) {
                return null;
            }

            $response = $this->client->get($this->baseUrl . '/ecommerce/orders/' . $orderId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $authToken
                ]
            ]);

            $orderData = json_decode($response->getBody(), true);

            if (!empty($orderData['transactions'])) {
                return end($orderData['transactions']);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Paymob get transaction by order ID failed: ' . $e->getMessage());
            return null;
        }
    }
    public function paymentCallback(array $data)
    {
        $isVailed = $this->validateWebhook($data);

        if (!$isVailed) {
            return [
                'success' => false,
                'message' => __('message.Invalid Webhook Data')
            ];
        }

        $payment = $this->paymobPaymentRepo->getPaymentByOrderId($data['obj']['order']['id']);

        if (!$payment) {
            return [
                'success' => false,
                'message' => __('message.Payment Not Found')
            ];
        }

        $status = $data['obj']['success'] == "true" ? 'success' : ($data['obj']['pending'] == "true" ? 'pending' : 'failed');

        $payment = $this->paymobPaymentRepo->update(
            $data['obj']['order']['id'],
            [
                'trnx_id' => $data['obj']['id'],
                'txn_response_code' => $data['obj']['data']['txn_response_code'] ?? null,
                'message' => $data['obj']['data']['message'],
                'pending' => $data['obj']['pending'] ? true : false,
                'success' => $data['obj']['success'] ? true : false,
                'type' => $data['obj']['source_data']['type'],
                'source_data_sub_type' => $data['obj']['source_data']['sub_type'],
                'status' => $status,
            ]
        );

        if ($payment->success == true) {
            $this->orderRepo->update($payment->my_order_id, ['payment_status' => 'paid']);
            $orderId = $payment->my_order_id;

            $order = $this->orderRepo->getOrderById($orderId);
            $carts = $this->cartRepo->getUserCart($order->user_id);

            if ($carts->isNotEmpty())
                $carts->each->delete();

            return [
                'success' => true,
                'message' => __('message.Payment Success')
            ];
        }

        return [
            'success' => false,
            'message' => __('message.Payment Failed')
        ];
    }
}
