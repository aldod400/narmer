<?php

namespace App\Services\Contracts;

interface CategoryServiceInterface
{
    public function getParentCategoriesPaginated(int $perPage, array $columns);
    public function getCategoryWithAllChildren(int $id);
    public function getCategoryById(string $id);
    public function getPopularCategory(int $limit = 20);
}
