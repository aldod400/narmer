<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'image',
        'popular',
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->select(
            'id',
            app()->getLocale() == 'ar' ? 'name_ar as name' : 'name_en as name',
            'slug',
            'image',
            'popular',
            'parent_id',
        );
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
