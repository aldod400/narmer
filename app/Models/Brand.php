<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'image',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
