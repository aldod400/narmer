<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    public static ?int $navigationSort = 1;
    public static function getNavigationGroup(): string
    {
        return __('message.Store Management');
    }

    public static function getNavigationLabel(): string
    {
        return __('message.Categories');
    }

    public static function getModelLabel(): string
    {
        return __('message.Category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('message.Categories');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name_en')
                    ->label(__('message.Name in English'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name_ar')
                    ->label(__('message.Name in Arabic'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Hidden::make('slug')
                    ->dehydrateStateUsing(function ($get) {
                        $slug = (string) str($get('name_en'))->slug();
                        $originalSlug = $slug;
                        $count = 1;

                        while (Category::where('slug', $slug)->exists()) {
                            $slug = "{$originalSlug}-" . $count++;
                        }

                        return $slug;
                    })
                    ->default(fn(callable $get) => (string) str($get('name_en'))->slug()),
                Forms\Components\FileUpload::make('image')
                    ->label(__('message.Image'))
                    ->directory('categories')
                    ->required()
                    ->image()
                    ->columnSpanFull(),
                Forms\Components\Select::make('parent_id')
                    ->label(__('message.Parent Category'))
                    ->relationship(
                        'parent',
                        app()->getLocale() === 'ar' ? 'name_ar' : 'name_en',
                        fn(Builder $query, $search, $record) => $query
                            ->where(function ($q) use ($search) {
                                $q->where('name_en', 'like', "%{$search}%")
                                    ->orWhere('name_ar', 'like', "%{$search}%");
                            })
                            ->when($record, fn($q) => $q->where('id', '!=', $record->id)) // Only apply this condition if $record is not null
                    )
                    ->nullable()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('popular')
                    ->label(__('message.Popular'))
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('message.Image'))
                    ->circular()
                    ->size(50)
                    ->toggleable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('name_ar')
                    ->searchable()
                    ->label(__('message.Name in Arabic'))
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('name_en')
                    ->label(__('message.Name in English'))
                    ->searchable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('parent.name_en')
                    ->label(__('message.Parent Category'))
                    ->getStateUsing(fn($record) => (app()->getLocale() == 'ar' ? $record->parent?->name_ar : $record->parent?->name_en) ?? '-')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('popular')
                    ->label(__('message.Popular'))
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label(__('message.Parent Category'))
                    ->relationship(
                        'parent',
                        app()->getLocale() === 'ar' ? 'name_ar' : 'name_en',
                        fn(Builder $query, $search) => $query->where(function ($q) use ($search) {
                            $q->where('name_en', 'like', "%{$search}%")
                                ->orWhere('name_ar', 'like', "%{$search}%");
                        })
                    ),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
