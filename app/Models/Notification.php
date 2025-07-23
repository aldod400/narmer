<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'body',
        'image',
        'type',
        'to',
        'user_id',
        'data',
        'is_read'
    ];
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(config('app.timezone'))->toDateTimeString();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->setTimezone(config('app.timezone'))->toDateTimeString();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
