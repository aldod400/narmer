<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\AttributeValue;
use App\Models\Attribute;
use App\Models\ProductAttributeValue;
use App\Enums\ProductStatus;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Store Management';
    public static ?int $navigationSort = 5;
    public static function getNavigationGroup(): string
    {
        return __('message.Store Management');
    }
    public static function getNavigationLabel(): string
    {
        return __('message.Products');
    }

    public static function getModelLabel(): string
    {
        return __('message.Product');
    }

    public static function getPluralModelLabel(): string
    {
        return __('message.Products');
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
                Forms\Components\Hidden::make('slug')
                    ->dehydrated(true)
                    ->dehydrateStateUsing(function ($get) {
                        $slug = (string) str($get('name_en'))->slug();
                        $originalSlug = $slug;
                        $count = 1;

                        while (Product::where('slug', $slug)->exists()) {
                            $slug = "{$originalSlug}-" . $count++;
                        }

                        return $slug;
                    })
                    ->default(fn(callable $get) => (string) str($get('name_en'))->slug()),
                Forms\Components\Textarea::make('description')
                    ->label(__('message.Description'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('price')
                    ->label(__('message.Price'))
                    ->minValue(1)
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('discount_price')
                    ->label(__('message.Discount Price'))
                    ->numeric()
                    ->minValue(1),
                Forms\Components\TextInput::make('quantity')
                    ->label(__('message.Quantity'))
                    ->numeric()
                    ->minValue(0)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label(__('message.Status'))
                    ->options([
                        1 => __('message.Active'),
                        0 => __('message.Inactive'),
                    ])
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('category_id')
                    ->label(__('message.Category'))
                    ->relationship('category', app()->getLocale() === 'ar' ? 'name_ar' : 'name_en')
                    ->searchable()
                    ->required()
                    ->preload(),
                Forms\Components\Select::make('brand_id')
                    ->label(__('message.Brand'))
                    ->relationship('brand', app()->getLocale() === 'ar' ? 'name_ar' : 'name_en')
                    ->searchable()
                    ->required()
                    ->preload(),
                Forms\Components\Repeater::make('images')
                    ->label(__('message.Images'))
                    ->relationship()
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label(__('message.Image'))
                            ->image()
                            ->directory('products')
                            ->required(),
                    ])
                    ->required()
                    ->visibleOn('create')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images.image')
                    ->label(__('message.Image'))
                    ->circular()
                    ->stacked()
                    ->limit(2)
                    ->limitedRemainingText(),
                Tables\Columns\TextColumn::make('name_ar')
                    ->label(__('message.Name in Arabic'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_en')
                    ->label(__('message.Name in English'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('message.Price'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_price')
                    ->label(__('message.Discount Price'))
                    ->numeric()
                    ->default(0)
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('message.Quantity'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->label(__('message.Status'))
                    ->boolean()
                    ->icon(fn($state) => $state === ProductStatus::ACTIVE ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn($state) => $state === ProductStatus::ACTIVE ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('category.name_en')
                    ->label(__('message.Category'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('brand.name_en')
                    ->label(__('message.Brand'))
                    ->sortable()
                    ->searchable(),
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
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('message.Category'))
                    ->relationship('category', app()->getLocale() === 'ar' ? 'name_ar' : 'name_en'),
                Tables\Filters\SelectFilter::make('brand_id')
                    ->label(__('message.Brand'))
                    ->relationship('brand', app()->getLocale() === 'ar' ? 'name_ar' : 'name_en'),
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
            RelationManagers\ImagesRelationManager::class,
            RelationManagers\ProductAttributesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
