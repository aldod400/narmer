<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\CategoryServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    protected $categoryService;
    public function __construct(CategoryServiceInterface $categoryService)
    {
        $this->categoryService = $categoryService;
    }
    public function getParentCategories(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);

        $categories = $this->categoryService->getParentCategoriesPaginated($perPage, ['*']);
        return Response::api(__('message.Success'), 200, true, null, $categories);
    }
    public function getChildrenCategory(int $id)
    {
        $categories = $this->categoryService->getCategoryWithAllChildren($id);
        return Response::api(__('message.Success'), 200, true, null, $categories);
    }
}
