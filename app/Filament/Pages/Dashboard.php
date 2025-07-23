<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CountCardWidget;
use Filament\Facades\Filament;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'dashboard';
    public static function getNavigationLabel(): string
    {
        return __('message.Dashboard');
    }
    public static function getPluralModelLabel(): string
    {
        return __('message.Dashboard');
    }
    public function getTitle(): string
    {
        return __('message.Dashboard');
    }

    public function getColumns(): int | string | array
    {
        return 3;
    }
}
