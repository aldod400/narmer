<?php

namespace App\Services\Implementations;

use App\Helpers\CategoryHelpers;
use App\Repository\Contracts\CategoryRepositoryInterface;
use App\Services\Contracts\CategoryServiceInterface;

class CategoryService implements CategoryServiceInterface
{
    protected $categoryRepo;
    public function __construct(CategoryRepositoryInterface $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
    }
    public function getParentCategoriesPaginated(int $perPage, array $columns)
    {
        return $this->categoryRepo->getParentCategoriesPaginated($perPage, $columns);
    }

    public function getCategoryWithAllChildren(int $id)
    {
        $categories = $this->categoryRepo->find($id);
        $categories = CategoryHelpers::loadChildren(collect([$categories]));
        return $categories->first();
    }
    public function getCategoryById(string $id)
    {
        return $this->categoryRepo->find($id);
    }
    public function getPopularCategory(int $total = 20)
    {
        $popularCategories = $this->categoryRepo->popular($total);

        $count = $popularCategories->count();

        if ($count < $total) {
            $needed = $total - $count;
            $excludeIds = $popularCategories->pluck('id')->toArray();

            $nonPopular = $this->categoryRepo->getNonPopularExcluding($excludeIds, $needed);

            $popularCategories = $popularCategories->concat($nonPopular);
        }

        return $popularCategories;
    }
}
