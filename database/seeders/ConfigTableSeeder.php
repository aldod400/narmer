<?php

namespace Database\Seeders;

use App\Models\Config;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfigTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Config::create([
            'android_app_version' => 1,
            'ios_app_version' => 1,
            'android_app_url' => 'https://play.google.com/store/apps/details?id=com.example.app',
            'ios_app_url' => 'https://apps.apple.com/app/id1234567890',
            'terms_and_conditions' => 'https://www.google.com',
            'privacy_policy' => 'https://www.google.com',
            'refund_policy' => 'https://www.google.com',
            'about_us' => 'https://www.google.com',
            'contact_us' => 'https://www.google.com',
            'facebook' => 'https://www.facebook.com',
            'twitter' => 'https://www.twitter.com',
            'instagram' => 'https://www.instagram.com',
            'linkedin' => 'https://www.linkedin.com',
            'tiktok' => 'https://www.tiktok.com',
            'whatsapp' => 'https://www.whatsapp.com',
        ]);
    }
}
