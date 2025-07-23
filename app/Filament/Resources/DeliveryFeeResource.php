<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryFeeResource\Pages;
use App\Filament\Resources\DeliveryFeeResource\RelationManagers;
use App\Models\DeliveryFee;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeliveryFeeResource extends Resource
{
    protected static ?string $model = DeliveryFee::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    public static ?int $navigationSort = 4;
    public static function shouldRegisterNavigation(): bool
    {
        return Setting::where('key', 'deliveryman')
            ->value('value') === '1' && Setting::where('key', 'delivery_fee_type')
            ->value('value') === 'area';
    }
    public static function getNavigationLabel(): string
    {
        return __('message.Delivery Fee');
    }

    public static function getNavigationGroup(): string
    {
        return __('message.Settings');
    }

    public static function getPluralModelLabel(): string
    {
        return __('message.Delivery Fees');
    }

    public static function getModelLabel(): string
    {
        return __('message.Delivery Fee');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('area_id')
                    ->label(__('message.Area'))
                    ->relationship('area', app()->getLocale() == 'ar' ? 'name_ar' : 'name_en')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('fee')
                    ->label(__('message.Fee'))
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('area_id')
                    ->label(__('message.Area'))
                    ->formatStateUsing(fn($record) => $record->area->{app()->getLocale() == 'ar' ? 'name_ar' : 'name_en'})
                    ->sortable(),
                Tables\Columns\TextColumn::make('fee')
                    ->label(__('message.Fee'))
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryFees::route('/'),
            'create' => Pages\CreateDeliveryFee::route('/create'),
            'edit' => Pages\EditDeliveryFee::route('/{record}/edit'),
        ];
    }
}
