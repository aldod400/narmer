<?php

namespace App\Filament\Pages;

use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use App\Models\Setting;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;

class DeliverySettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static string $view = 'filament.pages.delivery-settings';
    protected static ?int $navigationSort = 5;

    public function getTitle(): string
    {
        return __('message.DeliverySettings');
    }
    public static function getNavigationGroup(): ?string
    {
        return __('message.Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('message.DeliverySettings');
    }
    public static function getPluralModelLabel(): string
    {
        return __('message.DeliverySettings');
    }

    public static function getModelLabel(): string
    {
        return __('message.DeliverySetting');
    }
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'deliveryman' => Setting::where('key', 'deliveryman')->first()?->value,
            'delivery_fee_type' => Setting::where('key', 'delivery_fee_type')->first()?->value,
            'delivery_fee_fixed' => Setting::where('key', 'delivery_fee_fixed')->first()?->value,
            'delivery_fee_per_km' => Setting::where('key', 'delivery_fee_per_km')->first()?->value,
            'latitude' => Setting::where('key', 'latitude')->first()?->value,
            'longitude' => Setting::where('key', 'longitude')->first()?->value,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make(__('message.delivery_settings'))
                    ->schema([
                        Components\Toggle::make('deliveryman')
                            ->label(__('message.enable_delivery'))
                            ->live()
                            ->onColor('success')
                            ->offColor('danger'),

                        Components\Select::make('delivery_fee_type')
                            ->label(__('message.delivery_fee_type'))
                            ->options([
                                'fixed' => __('message.fixed_price'),
                                'per_km' => __('message.per_km'),
                                'area' => __('message.by_area'),
                            ])
                            ->required()
                            ->live()
                            ->visible(fn($get) => $get('deliveryman')),

                        Components\TextInput::make('delivery_fee_fixed')
                            ->label(__('message.fixed_delivery_fee'))
                            ->minValue(0)
                            ->numeric()
                            ->required()
                            ->visible(fn($get) =>
                            $get('deliveryman') && $get('delivery_fee_type') === 'fixed'),

                        Components\TextInput::make('delivery_fee_per_km')
                            ->label(__('message.per_km_delivery_fee'))
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->visible(fn($get) =>
                            $get('deliveryman') && $get('delivery_fee_type') === 'per_km'),
                        Components\TextInput::make('latitude')
                            ->live()
                            ->hidden()
                            ->dehydrated(true),
                        Components\TextInput::make('longitude')
                            ->live()
                            ->hidden()
                            ->dehydrated(true),
                        Map::make('location')
                            ->label(__('message.location'))
                            ->mapControls([
                                'mapTypeControl'    => true,
                                'scaleControl'      => true,
                                'streetViewControl' => true,
                            ])
                            ->clickable()
                            ->defaultLocation([
                                31.1840033388827,
                                29.91577952653098
                            ])
                            ->required()
                            ->visible(fn($get) => $get('deliveryman') && $get('delivery_fee_type') === 'per_km')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $set('latitude', $state['lat']);
                                $set('longitude', $state['lng']);
                            })
                            ->afterStateHydrated(function (callable $get, callable $set) {
                                $lat = $get('latitude');
                                $lng = $get('longitude');
                                if ($lat && $lng) {
                                    $set('location', ['lat' => (float) $lat, 'lng' => (float) $lng]);
                                }
                            })
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        if (isset($data['location']) && $data['location']) {
            $data['latitude'] = $data['location']['lat'];
            $data['longitude'] = $data['location']['lng'];
            unset($data['location']);
        }

        if (!$data['deliveryman']) {
            Setting::whereIn('key', [
                'deliveryman',
                'delivery_fee_type',
                'delivery_fee_fixed',
                'delivery_fee_per_km',
                'latitude',
                'longitude',
            ])->update(['value' => null]);

            $this->form->fill([
                'deliveryman' => false,
                'delivery_fee_type' => null,
                'delivery_fee_fixed' => null,
                'delivery_fee_per_km' => null,
                'latitude' => null,
                'longitude' => null,
            ]);
        } else {
            foreach ($data as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        Notification::make()
            ->title(__('message.settings_saved_successfully'))
            ->success()
            ->send();
        redirect()->to(DeliverySettings::getUrl());
    }
}
