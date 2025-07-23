<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\BannerServiceInterface;
use App\Services\Contracts\BrandServiceInterface;
use App\Services\Contracts\CategoryServiceInterface;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Http\Response;

class HomeController extends Controller
{
    protected $bannerService;
    protected $categoryService;
    protected $productService;
    protected $brandService;
    public function __construct(
        BannerServiceInterface $bannerService,
        CategoryServiceInterface $categoryService,
        ProductServiceInterface $productService,
        BrandServiceInterface $brandService
    ) {
        $this->categoryService = $categoryService;
        $this->bannerService = $bannerService;
        $this->productService = $productService;
        $this->brandService = $brandService;
    }
    public function getBanners()
    {
        $banners = $this->bannerService->getBanners();
        return Response::api(__('message.Success'), 200, true, null, $banners);
    }
    public function getBrands()
    {
        $brands = $this->brandService->getBrands(10);
        return Response::api(__('message.Success'), 200, true, null, $brands);
    }
    public function popularCategories()
    {
        $categories = $this->categoryService->getPopularCategory();
        return Response::api(__('message.Success'), 200, true, null, $categories);
    }
    public function getLatestProducts()
    {
        $products = $this->productService->getBestSellingProducts(30);

        return Response::api(__('message.Success'), 200, true, null, $products);
    }
}
