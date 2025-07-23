<?php

namespace App\Helpers;

class CategoryHelpers
{
    public static function loadChildren($categories)
    {
        foreach ($categories as $category) {
            $category->children = $category->children()->get();
            if ($category->children->isNotEmpty())
                $category->children = self::loadChildren($category->children);
            else
                $category->children = collect();
        }
        return $categories;
    }
    public static function collectCategoryIds($categories, &$ids = [])
    {
        foreach ($categories as $category) {
            $ids[] = $category->id;

            if (!empty($category->children))
                self::collectCategoryIds($category->children, $ids);
        }
        return $ids;
    }
}
