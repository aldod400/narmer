<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\Deliveryman\OrderController as DeliverymanOrderController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'lang'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::group(['middleware' => 'auth:api', 'prefix' => 'profile'], function () {
            Route::get('/', [AuthController::class, 'profile'])->name('profile');
            Route::post('/update', [AuthController::class, 'updateProfile'])->name('updateProfile');
            Route::post('/delete', [AuthController::class, 'deleteProfile'])->name('deleteProfile');
        });
    });
    Route::get('/config', [ConfigController::class, 'getConfig']);

    Route::group(['prefix' => 'home'], function () {
        Route::get('/banners', [HomeController::class, 'getBanners'])->name('banners');
        Route::get('/brands', [HomeController::class, 'getBrands'])->name('brands');
        Route::get('/categories', [HomeController::class, 'popularCategories'])->name('popular.categories');
        Route::get('/products', [HomeController::class, 'getLatestProducts'])->name('latest.products');
    });
    Route::group(['prefix' => 'categories'], function () {
        Route::get('/parent', [CategoryController::class, 'getParentCategories'])->name('getParentCategories');
        Route::get('/children/{id}', [CategoryController::class, 'getChildrenCategory'])->name('getChildrenCategories');
    });
    Route::group(['prefix' => 'products'], function () {
        Route::get('/', [ProductController::class, 'index'])->name('getAllProducts');
        Route::get('/category/{categoryId}', [ProductController::class, 'getProductsFromCategoryAndChildren'])->name('getProductsFromCategoryAndChildren');
        Route::get('/{id}', [ProductController::class, 'getProduct']);
        Route::get('/details/{id}', [ProductController::class, 'show'])->name('productDetails');
    });
    Route::resource('carts', CartController::class)->middleware('auth:api');
    Route::get('/cart/delivery-fee', [CartController::class, 'getDeliveryFee'])->middleware('auth:api');
    Route::resource('addresses', AddressController::class, [
        'only' => [
            'index',
            'store',
            'update',
            'destroy'
        ]
    ])->middleware('auth:api');

    Route::resource('wishlists', WishlistController::class, [
        'only' => [
            'index',
            'store',
            'destroy'
        ]
    ])->middleware('auth:api');
    Route::get('/coupons/{code}', [CouponController::class, 'validateCoupon'])
        ->name('validateCoupon')->middleware('auth:api');

    Route::group(['prefix' => 'orders'], function () {
        Route::get('/', [OrderController::class, 'index'])->middleware('auth:api');
        Route::post('/', [OrderController::class, 'store'])->middleware('auth:api');
        Route::get('/{id}', [OrderController::class, 'show'])->middleware('auth:api');
    });

    Route::post('/payment/callback', [PaymentController::class, 'callback']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/admin/save-token', [AuthController::class, 'saveFcmTokenToAdmin'])->name('admin.save-fcm-token');
    Route::group(['prefix' => 'deliveryman', 'middleware' => 'CheckDeliveryman'], function () {
        Route::get('/orders', [DeliverymanOrderController::class, 'index']);
        Route::post('/orders/status/{id}', [DeliverymanOrderController::class, 'changeStatus']);
    });
});
