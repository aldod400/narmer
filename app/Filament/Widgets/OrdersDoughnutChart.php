<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use Filament\Widgets\ChartWidget;
use App\Models\Order;
use App\Models\Setting;

class OrdersDoughnutChart extends ChartWidget
{
    protected static ?string $heading = 'Order Statstics';
    protected static ?string $maxHeight = '300px';
    public function getHeading(): string
    {
        return __('message.Order Statstics');
    }
    protected function getData(): array
    {
        $statuses = [
            OrderStatus::PENDING->value => __('message.Pending'),
            OrderStatus::CONFIRMED->value => __('message.Confirmed'),
            OrderStatus::PREPARING->value => __('message.Preparing'),
            OrderStatus::READY->value => __('message.Ready'),
        ];

        if (Setting::where('key', 'deliveryman')->value('value') === '1') {
            $statuses += [
                OrderStatus::ONDELIVERY->value => __('message.On Delivery'),
                OrderStatus::DELIVERED->value => __('message.Delivered'),
            ];
        }

        $statuses += [
            OrderStatus::CANCELED->value => __('message.Canceled'),
        ];

        $data = collect($statuses)->mapWithKeys(function ($label, $status) {
            return [$status => Order::where('status', $status)->count()];
        });

        $backgroundColors = [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF',
            '#FF9F40',
            '#E7E9ED',
        ];

        return [
            'datasets' => [
                [
                    'data' => $data->values(),
                    'backgroundColor' => array_slice($backgroundColors, 0, count($data)),
                    'borderColor' => '#fff',
                    'borderWidth' => 2,
                    'hoverOffset' => 10,
                ],
            ],
            'labels' => collect($statuses)->values(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'rtl' => true,
                    'labels' => [
                        'font' => [
                            'family' => 'Tajawal, sans-serif',
                            'size' => 14,
                        ],
                        'padding' => 20,
                        'usePointStyle' => true,
                    ]
                ],
                'tooltip' => [
                    'enabled' => true,
                    'rtl' => true,
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => false,
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'display' => false,
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'cutout' => '55%',
            'animation' => [
                'animateScale' => true,
                'animateRotate' => true,
            ],
            // 'maintainAspectRatio' => false,
        ];
    }
}
