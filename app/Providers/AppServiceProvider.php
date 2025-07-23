<?php

namespace App\Providers;

use App\Repository\Contracts\AddressRepositoryInterface;
use App\Repository\Contracts\ConfigRepositoryInterface;
use App\Repository\Eloquent\ConfigRepository;
use App\Services\Contracts\ConfigServiceInterface;
use App\Services\Implementations\ConfigService;
use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;
use App\Repository\Contracts\AuthRepositoryInterface;
use App\Repository\Contracts\BannerRepositoryInterface;
use App\Repository\Contracts\BrandRepositoryInterface;
use App\Repository\Contracts\CartRepositoryInterface;
use App\Repository\Contracts\CategoryRepositoryInterface;
use App\Repository\Contracts\CityRepositoryInterface;
use App\Repository\Contracts\CouponRepositoryInterface;
use App\Repository\Contracts\NotificationRepositoryInterface;
use App\Repository\Contracts\OrderRepositoryInterface;
use App\Repository\Contracts\PaymobPaymentRepositoryInterface;
use App\Repository\Contracts\ProductRepositoryInterface;
use App\Repository\Contracts\SettingRepositoryInterface;
use App\Repository\Contracts\WishlistRepositoryInterface;
use App\Repository\Eloquent\AddressRepository;
use App\Repository\Eloquent\AuthRepository;
use App\Repository\Eloquent\BannerRepository;
use App\Repository\Eloquent\BrandRepository;
use App\Repository\Eloquent\CartRepository;
use App\Repository\Eloquent\CategoryRepository;
use App\Repository\Eloquent\CityRepository;
use App\Repository\Eloquent\CouponRepository;
use App\Repository\Eloquent\NotificationRepository;
use App\Repository\Eloquent\OrderRepository;
use App\Repository\Eloquent\PaymobPaymentRepository;
use App\Repository\Eloquent\ProductRepository;
use App\Repository\Eloquent\SettingRepository;
use App\Repository\Eloquent\WishlistRepository;
use App\Services\Contracts\AddressServiceInterface;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\BannerServiceInterface;
use App\Services\Contracts\BrandServiceInterface;
use App\Services\Contracts\CartServiceInterface;
use App\Services\Contracts\CategoryServiceInterface;
use App\Services\Contracts\CouponServiceInterface;
use App\Services\Contracts\DeliveryFeeServiceInterface;
use App\Services\Contracts\FirebaseServiceInterface;
use App\Services\Contracts\NotificationServiceInterface;
use App\Services\Contracts\OrderServiceInterface;
use App\Services\Contracts\PaymobServiceInterface;
use App\Services\Contracts\ProductServiceInterface;
use App\Services\Contracts\WishlistServiceInterface;
use App\Services\Implementations\AddressService;
use App\Services\Implementations\AuthService;
use App\Services\Implementations\BannerService;
use App\Services\Implementations\BrandService;
use App\Services\Implementations\CartService;
use App\Services\Implementations\CategoryService;
use App\Services\Implementations\CouponService;
use App\Services\Implementations\DeliveryFeeService;
use App\Services\Implementations\FirebaseService;
use App\Services\Implementations\NotificationService;
use App\Services\Implementations\OrderService;
use App\Services\Implementations\PaymobService;
use App\Services\Implementations\ProductService;
use App\Services\Implementations\WishlistService;
use App\Strategies\Contracts\Login\LoginStrategyInterface;
use App\Strategies\Implementations\Login\EmailLoginStrategy;
use App\Strategies\Implementations\Login\PhoneLoginStrategy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ConfigServiceInterface::class, ConfigService::class);
        $this->app->bind(ConfigRepositoryInterface::class, ConfigRepository::class);
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(CategoryServiceInterface::class, CategoryService::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(BannerRepositoryInterface::class, BannerRepository::class);
        $this->app->bind(BannerServiceInterface::class, BannerService::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);
        $this->app->bind(BrandServiceInterface::class, BrandService::class);
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(CartServiceInterface::class, CartService::class);
        $this->app->bind(WishlistRepositoryInterface::class, WishlistRepository::class);
        $this->app->bind(WishlistServiceInterface::class, WishlistService::class);
        $this->app->bind(AddressRepositoryInterface::class, AddressRepository::class);
        $this->app->bind(AddressServiceInterface::class, AddressService::class);
        $this->app->bind(CityRepositoryInterface::class, CityRepository::class);
        $this->app->bind(CouponRepositoryInterface::class, CouponRepository::class);
        $this->app->bind(CouponServiceInterface::class, CouponService::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(DeliveryFeeServiceInterface::class, DeliveryFeeService::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(PaymobServiceInterface::class, PaymobService::class);
        $this->app->bind(PaymobPaymentRepositoryInterface::class, PaymobPaymentRepository::class);
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);
        $this->app->bind(NotificationRepositoryInterface::class, NotificationRepository::class);
        $this->app->bind(FirebaseServiceInterface::class, FirebaseService::class);

        $this->app->bind(AuthServiceInterface::class, function ($app) {
            return new AuthService(
                $app->make(AuthRepositoryInterface::class),
                $app->make(LoginStrategyInterface::class . '_email'),
                $app->make(LoginStrategyInterface::class . '_phone')
            );
        });

        $this->app->bind(LoginStrategyInterface::class . '_email', EmailLoginStrategy::class);
        $this->app->bind(LoginStrategyInterface::class . '_phone', PhoneLoginStrategy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Response::macro(
            'api',
            function ($message, $statusCode = 200, $status = true, $errorNum = null, $data = null) {
                $responseData = [
                    'status' => $status,
                    'errorNum' => $errorNum,
                    'message' => $message,
                ];

                if ($data)
                    $responseData = array_merge($responseData, ['data' => $data]);

                return response()->json($responseData, $statusCode);
            }
        );
    }
}
