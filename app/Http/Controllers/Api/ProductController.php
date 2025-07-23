<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    protected $productService;
    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }
    public function index(Request $request)
    {
        $search = $request->input('search', null);
        $perPage = (int) $request->input('per_page', 10);

        $products = $this->productService->getProducts($search, $perPage);

        return Response::api(__('message.Success'), 200, true, null, $products);
    }
    public function getProductsFromCategoryAndChildren(Request $request, int $categoryId)
    {
        $perPage = (int) $request->input('per_page', 10);

        $products = $this->productService
            ->getProductsFromCategoryAndChildren($categoryId, $perPage);

        return Response::api(__('message.Success'), 200, true, null, $products);
    }
    public function getProduct(int $id)
    {
        $product = $this->productService->getProductById($id);
        return Response::api(__('message.Success'), 200, true, null, $product);
    }
    public function show(int $id)
    {
        $product = $this->productService->productDetails($id);
        return Response::api(__('message.Success'), 200, true, null, $product);
    }
}
