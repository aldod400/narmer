<?php

namespace App\Services\Implementations;

use App\Helpers\CategoryHelpers;
use App\Repository\Contracts\CategoryRepositoryInterface;
use App\Repository\Contracts\ProductRepositoryInterface;
use App\Services\Contracts\CategoryServiceInterface;
use App\Services\Contracts\ProductServiceInterface;

class ProductService implements ProductServiceInterface
{
    protected $productRepo;
    protected $categoryRepo;
    public function __construct(
        ProductRepositoryInterface $productRepo,
        CategoryRepositoryInterface $categoryRepo
    ) {
        $this->productRepo = $productRepo;
        $this->categoryRepo = $categoryRepo;
    }
    public function getProductById(int $id)
    {
        return $this->productRepo->find($id);
    }
    public function getProducts(?string $search = null, int $perPage)
    {
        $products = $this->productRepo->paginate($search, $perPage);

        $products->map(function ($product) {
            $product->image = optional($product->images->first())->image;
            unset($product->images);
            return $product;
        });
        return $products;
    }
    public function getBestSellingProducts(int $take)
    {
        $products = $this->productRepo->getBestSelling($take);

        if ($products->count() < $take) {
            $remaining = $take - $products->count();
            $latest = $this->productRepo->latest($remaining);

            $products = $products->merge(
                $latest->whereNotIn('id', $products->pluck('id'))
            );
        }

        $products->map(function ($product) {
            $product->image = optional($product->images->first())->image;
            unset($product->images);
            return $product;
        });
        return $products;
    }
    public function getProductsFromCategoryAndChildren(int $categoryId, int $perPage)
    {
        $category = $this->categoryRepo->find($categoryId);
        $categories = CategoryHelpers::loadChildren(collect([$category]));
        $categoryIds = CategoryHelpers::collectCategoryIds($categories);
        $products = $this->productRepo->getProductsByCategoryIds($categoryIds, $perPage);
        $products->map(function ($product) {
            $product->image = optional($product->images->first())->image;
            unset($product->images);
            return $product;
        });
        return $products;
    }
    public function productDetails(int $id)
    {
        $product = $this->productRepo->productDetails($id);
        if ($product && $product->images) {
            $product->images = $product->images->pluck('image')->toArray();
        }
        return $product;
    }
}
