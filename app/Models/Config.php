<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $fillable = [
        'android_app_version',
        'ios_app_version',
        'android_app_url',
        'ios_app_url',
        'terms_and_conditions',
        'privacy_policy',
        'refund_policy',
        'about_us',
        'contact_us',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'tiktok',
        'whatsapp',
    ];
}
