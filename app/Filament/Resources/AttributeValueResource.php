<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeValueResource\Pages;
use App\Filament\Resources\AttributeValueResource\RelationManagers;
use App\Models\AttributeValue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttributeValueResource extends Resource
{
    protected static ?string $model = AttributeValue::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string
    {
        return __('message.Store Management');
    }
    public static function getNavigationLabel(): string
    {
        return __('message.Attribute Values');
    }

    public static function getModelLabel(): string
    {
        return __('message.Attribute Value');
    }

    public static function getPluralModelLabel(): string
    {
        return __('message.Attribute Values');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('value')
                    ->label(__('message.Value'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('attribute_id')
                    ->relationship('attribute', app()->getLocale() == 'ar' ? 'name_ar' : 'name_en')
                    ->required()
                    ->label(__('message.Attribute'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('value')
                    ->label(__('message.Value'))
                    ->searchable(),
                Tables\Columns\TextColumn::make(app()->getLocale() == 'ar' ? 'attribute.name_ar' : 'attribute.name_en')
                    ->label(__('message.Attribute'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
            'index' => Pages\ListAttributeValues::route('/'),
            'create' => Pages\CreateAttributeValue::route('/create'),
            'edit' => Pages\EditAttributeValue::route('/{record}/edit'),
        ];
    }
}
