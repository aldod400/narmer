<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class OrdersLineChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';
    protected static ?string $maxHeight = '300px';
    protected int|string|array $columnSpan = 2;
    public function getHeading(): string
    {
        return __('message.Order Statstics');
    }
    protected function getData(): array
    {
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();

        $orders = Order::query()
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy('date')
            ->pluck('count', 'date');

        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d/m');
            $data[] = $orders[$date] ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data,
                    'borderColor' => '#36A2EB',
                    'fill' => false,
                ],
            ],
        ];
    }


    protected function getType(): string
    {
        return 'line';
    }
}
