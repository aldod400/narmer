<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductAttributeValue;
use App\Models\Product;
use App\Models\ProductAttributeValue as ProductAttributeValueModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class ProductAttributesRelationManager extends RelationManager
{
    protected static string $relationship = 'productAttributes';
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('message.Product Attributes');
    }
    public static function getNavigationLabel(): string
    {
        return __('message.Product Attributes');
    }

    public static function getModelLabel(): string
    {
        return __('message.Product Attribute');
    }

    public static function getPluralModelLabel(): string
    {
        return __('message.Product Attributes');
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('attribute_id')
                    ->label(__('message.Attribute'))
                    ->options(
                        Attribute::query()
                            ->pluck(
                                app()->getLocale() === 'ar' ? 'name_ar' : 'name_en',
                                'id'
                            )
                    )
                    ->reactive()
                    ->afterStateHydrated(function (callable $set, $record) {
                        if (! $record) {
                            return;
                        }
                        $set(
                            'attribute_id',
                            optional($record->attributeValue)->attribute_id
                        );
                    })
                    ->afterStateUpdated(fn(callable $set) => $set('attribute_value_id', null))
                    ->required()
                    ->searchable()
                    ->getOptionLabelUsing(fn($value) => Attribute::find($value)?->{app()->getLocale() === 'ar' ? 'name_ar' : 'name_en'}),

                Forms\Components\Select::make('attribute_value_id')
                    ->label(__('message.Attribute Value'))
                    ->options(function (callable $get) {
                        $attributeId = $get('attribute_id');
                        if (! $attributeId) {
                            return [];
                        }

                        return AttributeValue::where('attribute_id', $attributeId)
                            ->pluck('value', 'id');
                    })
                    ->afterStateHydrated(function (callable $set, $state, $record) {
                        $set(
                            'attribute_value_id',
                            $state ?? optional($record)->attribute_value_id
                        );
                    })
                    ->required()
                    ->reactive()
                    ->searchable()
                    ->getOptionLabelUsing(fn($value) => AttributeValue::find($value)?->value)
                    ->rule(function (callable $get) {
                        return Rule::unique('product_attribute_values', 'attribute_value_id')
                            ->where('product_id', $this->getOwnerRecord()->id)
                            ->ignore($get('id'));
                    }),
                Forms\Components\TextInput::make('price')
                    ->label(__('message.Price'))
                    ->numeric()
                    ->required()
                    ->hidden(function (callable $get) {
                        return empty($get('attribute_value_id'));
                    })
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('value')
            ->columns([
                Tables\Columns\TextColumn::make('attributeValue.attribute.name')
                    ->label(__('message.Attribute'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('attributeValue.value')
                    ->label(__('message.Attribute Value'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('message.Price'))
                    ->sortable()
                    ->searchable(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
