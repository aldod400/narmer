<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Filament\Resources\CouponResource\RelationManagers;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Enums\DiscountType;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    public static ?int $navigationSort = 7;
    public static function getNavigationGroup(): string
    {
        return __('message.Store Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('message.Coupons');
    }

    public static function getModelLabel(): string
    {
        return __('message.Coupon');
    }

    public static function getPluralModelLabel(): string
    {
        return __('message.Coupons');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label(__('message.Code'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Select::make('discount_type')
                    ->label(__('message.Discount Type'))
                    ->options([
                        DiscountType::FIXED->value => __('message.Fixed Amount'),
                        DiscountType::PERCENTAGE->value => __('message.Percentage'),
                    ])
                    ->required(),
                Forms\Components\TextInput::make('discount_value')
                    ->label(__('message.Discount Value'))
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('expiry_date')
                    ->label(__('message.Expiry Date')),

                Forms\Components\TextInput::make('usage_limit')
                    ->label(__('message.Usage Limit'))
                    ->required()
                    ->numeric()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('message.Code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('discount_type')
                    ->label(__('message.Discount Type')),
                Tables\Columns\TextColumn::make('discount_value')
                    ->label(__('message.Discount Value'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label(__('message.Expiry Date'))
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_limit')
                    ->label(__('message.Usage Limit'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('message.Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('message.Updated At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\UserCouponsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
