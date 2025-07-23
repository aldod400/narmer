<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en'
    ];

    public function areas()
    {
        return $this->hasMany(Area::class);
    }
}
