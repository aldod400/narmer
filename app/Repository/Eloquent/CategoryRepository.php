<?php

namespace App\Repository\Eloquent;

use App\Models\Category;
use App\Repository\Contracts\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function find(int $id)
    {
        return Category::select(
            'id',
            app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name',
            'slug',
            'image',
            'popular',
            'parent_id',
        )->findOrFail($id);
    }
    public function getParentCategoriesPaginated(int $perPage, array $columns)
    {
        return Category::whereNull('parent_id')
            ->select(
                'id',
                app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name',
                'slug',
                'image',
                'popular',
                'parent_id',
            )->paginate($perPage, $columns);
    }
    public function popular(int $limit = 20)
    {
        return Category::where('popular', true)
            ->whereNull('parent_id')
            ->latest()
            ->take($limit)
            ->select(
                'id',
                app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name',
                'slug',
                'image',
                'popular',
                'parent_id',
            )->get();
    }

    public function getNonPopularExcluding(array $excludeIds, int $limit)
    {
        return Category::where('popular', false)
            ->whereNull('parent_id')
            ->whereNotIn('id', $excludeIds)
            ->latest()
            ->take($limit)
            ->select(
                'id',
                app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name',
                'slug',
                'image',
                'popular',
                'parent_id',
            )->get();
    }
}
