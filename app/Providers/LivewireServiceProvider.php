<?php

namespace App\Providers;

use App\Filament\Pages\Widgets\AddAddressModal;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LivewireServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Livewire::component('add-address-modal', AddAddressModal::class);
    }
}
