<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AreaResource\Pages;
use App\Filament\Resources\AreaResource\RelationManagers;
use App\Models\Area;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Setting;

class AreaResource extends Resource
{
    protected static ?string $model = Area::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    public static ?int $navigationSort = 4;
    public static function getNavigationGroup(): string
    {
        return __('message.Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('message.Areas');
    }

    public static function getModelLabel(): string
    {
        return __('message.Area');
    }

    public static function getPluralModelLabel(): string
    {
        return __('message.Areas');
    }
    public static function shouldRegisterNavigation(): bool
    {
        return Setting::where('key', 'deliveryman')->value('value') === '1'
            && Setting::where('key', 'delivery_fee_type')->value('value') === 'area';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name_ar')
                    ->label(__('message.Name in Arabic'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name_en')
                    ->label(__('message.Name in English'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('city_id')
                    ->label(__('message.City'))
                    ->relationship('city', app()->getLocale() == 'ar' ? 'name_ar' : 'name_en')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('price')
                    ->label(__('message.Price'))
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')
                    ->label(__('message.Name in Arabic'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_en')
                    ->label(__('message.Name in English'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('message.Price'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('city_id')
                    ->label(__('message.City'))
                    ->formatStateUsing(fn($record) => $record->city->{app()->getLocale() == 'ar' ? 'name_ar' : 'name_en'})
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAreas::route('/'),
            'create' => Pages\CreateArea::route('/create'),
            'edit' => Pages\EditArea::route('/{record}/edit'),
        ];
    }
}
