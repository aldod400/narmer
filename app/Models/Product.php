<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'description',
        'price',
        'discount_price',
        'quantity',
        'status',
        'brand_id',
        'category_id',
    ];
    protected $casts = [
        'status' => ProductStatus::class,
    ];
    protected $appends = [
        'rate',
    ];

    public function getRateAttribute()
    {
        return (float) $this->hasMany(Review::class)->avg('rating') ?? 0;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function attribute()
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute_values', 'product_id')
            ->join('attribute_values', 'product_attribute_values.attribute_value_id', '=', 'attribute_values.id')
            ->where('attribute_values.attribute_id', '=', DB::raw('attributes.id'))
            ->withPivot('id', 'attribute_value_id', 'price')
            ->distinct();
    }

    public function productAttributes()
    {
        return $this->hasMany(ProductAttributeValue::class)
            ->with([
                'attributeValue:id,attribute_id,value',
                'attributeValue.attribute:id,' . (app()->getLocale() === 'ar' ? 'name_ar' : 'name_en') . ' as name',
            ]);
    }
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
