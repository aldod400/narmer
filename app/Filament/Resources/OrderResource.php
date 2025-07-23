<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Setting;
use App\Models\User;
use App\Enums\UserType;
use App\Enums\OrderStatus;
use Illuminate\Support\Str;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): string
    {
        return __('message.Store Management');
    }
    public static function getNavigationLabel(): string
    {
        return __('message.Orders');
    }

    public static function getModelLabel(): string
    {
        return __('message.Order');
    }

    public static function getPluralModelLabel(): string
    {
        return __('message.Orders');
    }
    public static function getNavigationBadge(): ?string
    {
        $pendingOrders = static::getModel()::where('status', OrderStatus::PENDING)->count();
        return $pendingOrders > 0 ? $pendingOrders  . ' ' . __('message.Pending') : null;
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->orderByDesc('id');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('deliveryman_id')
                    ->label(__('message.Deliveryman'))
                    ->options(function () {
                        return User::where('user_type', UserType::DELIVERYMAN)
                            ->get()
                            ->mapWithKeys(fn($user) => [
                                $user->id => $user->name . ' - ' . $user->phone,
                            ]);
                    })
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->visible(fn() => Setting::where('key', 'deliveryman')->value('value') === '1')
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $allowedStatuses = [
                            OrderStatus::READY->value,
                            OrderStatus::ONDELIVERY->value,
                            OrderStatus::DELIVERED->value,
                            OrderStatus::CANCELED->value,
                        ];

                        $currentStatus = $get('status');

                        if (!in_array($currentStatus, $allowedStatuses)) {
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title(__('message.Deliveryman can only be assigned when order status is Ready, On Delivery, Delivered or Canceled'))
                                ->send();

                            $set('deliveryman_id', null);
                        }
                    })
                    ->default(null)
                    ->columnSpanFull(),

                Forms\Components\Select::make('status')
                    ->label(__('message.Status'))
                    ->options(function () {
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
                        return $statuses;
                    })
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Select::make('payment_status')
                    ->label(__('message.Payment Status'))
                    ->options([
                        'paid' => __('message.Paid'),
                        'unpaid' => __('message.Unpaid'),
                    ])
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('message.#ID'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('message.User Name'))
                    ->sortable()
                    ->default('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label(__('message.User Phone'))
                    ->sortable()
                    ->default('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deliveryman.name')
                    ->visible(fn() => Setting::where('key', 'deliveryman')->value('value') === '1')
                    ->label(__('message.Deliveryman'))
                    ->sortable()
                    ->default('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('message.Status'))

                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'preparing' => 'primary',
                        'ready' => 'success',
                        'on_delivery' => 'purple',
                        'delivered' => 'success',
                        'canceled' => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(function (string $state) {
                        return __('message.' . $state);
                    })
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('message.Payment Method'))
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'info',
                        'visa' => 'success',
                        'wallet' => 'warning',
                        default => 'gray'
                    })
                    ->formatStateUsing(function (string $state) {
                        return __('message.' . Str::ucfirst($state));
                    })
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label(__('message.Payment Status'))
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(function (string $state) {
                        return __('message.' . Str::ucfirst($state));
                    })
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_type')
                    ->label(__('message.Order Type'))
                    ->color(fn(string $state): string => match ($state) {
                        'online' => 'success',
                        'pos' => 'primary',
                        default => 'gray'
                    })
                    ->formatStateUsing(function (string $state) {
                        return __('message.' . Str::ucfirst($state));
                    })
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('address.address')
                    ->label(__('message.Address'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('order.coupon.code')
                    ->label(__('message.Coupon'))
                    ->default('-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount')
                    ->label(__('message.Discount'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn(string $state) => $state . ' ' . __('message.Currency')),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label(__('message.Sub Total'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn(string $state) => $state . ' ' . __('message.Currency')),
                Tables\Columns\TextColumn::make('delivery_fee')
                    ->label(__('message.Delivery Fee'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn(string $state) => $state . ' ' . __('message.Currency')),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('message.Total Price'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn(string $state) => $state . ' ' . __('message.Currency')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make(app()->getLocale() == 'ar' ? 'address.city.name_ar' : 'address.city.name_en')
                    ->label(__('message.City'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make(app()->getLocale() == 'ar' ? 'area.name_ar' : 'area.name_en')
                    ->label(__('message.Area'))
                    ->visible(fn() => Setting::where('key', 'deliveryman')
                        ->value('value') === '1' && Setting::where('key', 'delivery_fee_type')
                        ->value('value') === 'by_area')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('message.Status'))
                    ->options(function () {
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
                        return $statuses;
                    }),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label(__('message.Payment Status'))
                    ->options([
                        'paid' => __('message.Paid'),
                        'unpaid' => __('message.Unpaid'),
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label(__('message.Payment Method'))
                    ->options([
                        'cash' => __('message.Cash'),
                        'online' => __('message.Online'),
                    ]),
                Tables\Filters\SelectFilter::make('order_type')
                    ->label(__('message.Order Type'))
                    ->options([
                        'online' => __('message.Online'),
                        'pos' => __('message.POS'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderDetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }
}
