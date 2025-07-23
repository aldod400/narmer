<?php

namespace App\Repository\Contracts;

interface CategoryRepositoryInterface
{
    public function find(int $id);
    public function getParentCategoriesPaginated(int $perPage, array $columns);
    public function popular(int $limit = 20);
    public function getNonPopularExcluding(array $excludeIds, int $limit);
}
