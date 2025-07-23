<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderDetails';

    protected static ?string $title = 'OrderDetails';
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('message.OrderDetails');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('product.images.image')
                    ->label(__('message.Image'))
                    ->circular()
                    ->stacked()
                    ->limit(2)
                    ->limitedRemainingText(),
                Tables\Columns\TextColumn::make(app()->getLocale() == 'ar' ? 'product.name_ar' : 'product.name_en')
                    ->label(__('message.Product'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('message.Quantity'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('message.Price'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.discount_price')
                    ->label(__('message.Discount Price'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('attributeValues')
                    ->label(__('message.Attributes'))
                    ->formatStateUsing(function ($record) {
                        $grouped = $record->attributeValues
                            ->filter(fn($attrValueRecord) => $attrValueRecord->attributeValue && $attrValueRecord->attributeValue->attribute)
                            ->groupBy(function ($attrValueRecord) {
                                $attribute = $attrValueRecord->attributeValue->attribute;
                                return app()->getLocale() === 'ar' ? $attribute->name_ar : $attribute->name_en;
                            });

                        return $grouped->map(function ($items, $attributeName) {
                            $values = $items->map(function ($item) {
                                return '<span style="padding:4px 8px; background-color:#22C55E;border-radius:6px; color:white;font-size:12px">' . e($item->attributeValue->value) . '</span>';
                            })->implode(', ');

                            return '<span class="text-gray-700 font-semibold" style="margin:20px 0">' . e($attributeName) . '</span>: ' . $values;
                        })->implode('<br>');
                    })
                    ->html()
                    ->wrap()
            ]);
    }
}
