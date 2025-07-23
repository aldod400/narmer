<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryFee extends Model
{
    protected $fillable = [
        'area_id',
        'fee',
    ];
    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
