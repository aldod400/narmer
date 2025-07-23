<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate([
            'key' => 'deliveryman',
            'value' => 0,
        ]);
        Setting::updateOrCreate([
            'key' => 'delivery_fee_type',
            'value' => '',
        ]);

        Setting::updateOrCreate([
            'key' => 'delivery_fee_fixed',
            'value' => '',
        ]);

        Setting::updateOrCreate([
            'key' => 'delivery_fee_per_km',
            'value' => '',
        ]);
        Setting::updateOrCreate([
            'key' => 'latitude',
            'value' => '',
        ]);
        Setting::updateOrCreate([
            'key' => 'longitude',
            'value' => '',
        ]);
    }
}
