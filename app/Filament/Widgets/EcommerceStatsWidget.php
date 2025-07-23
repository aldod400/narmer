<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class EcommerceStatsWidget extends BaseWidget
{
    protected function getCards(): array
    {
        $productStats = Product::groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->select(DB::raw('COUNT(*) as count'))
            ->pluck('count')
            ->toArray();

        $orderStats = Order::groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->select(DB::raw('COUNT(*) as count'))
            ->pluck('count')
            ->toArray();

        $userStats = User::groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->select(DB::raw('COUNT(*) as count'))
            ->pluck('count')
            ->toArray();

        $salesStats = Order::groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->select(DB::raw('SUM(total) as total'))
            ->pluck('total')
            ->toArray();

        return [
            Stat::make(__('message.Products'), Product::count())
                ->description(__('message.Total Products'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->chart($productStats)
                ->color('primary'),

            Stat::make(__('message.Orders'), Order::count())
                ->description(__('message.Total Orders'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->chart($orderStats)
                ->color('warning'),

            Stat::make(__('message.Users'), User::count())
                ->description(__('message.Total Users'))
                ->descriptionIcon('heroicon-m-user-group')
                ->chart($userStats)
                ->color('purple'),

            Stat::make(__('message.Income'), number_format(Order::sum('total')) . ' ' . __('message.Currency'))
                ->description(__('message.Total Income From All Orders'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($salesStats)
                ->color('success'),
        ];
    }
}
