<?php

namespace App\Filament\Resources;

use App\Enums\UserType;
use App\Filament\Resources\ReviewResource\Pages;
use App\Filament\Resources\ReviewResource\RelationManagers;
use App\Models\Review;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?int $navigationSort = 8;
    public static function getNavigationGroup(): string
    {
        return __('message.Store Management');
    }
    public static function getNavigationLabel(): string
    {
        return __('message.Reviews');
    }

    public static function getModelLabel(): string
    {
        return __('message.Review');
    }

    public static function getPluralModelLabel(): string
    {
        return __('message.Reviews');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('rating')
                    ->label(__('message.Rating'))
                    ->schema([
                        Forms\Components\Radio::make('rating')
                            ->label(__('message.Rating'))
                            ->options([
                                1 => '⭐️',
                                2 => '⭐️⭐️',
                                3 => '⭐️⭐️⭐️',
                                4 => '⭐️⭐️⭐️⭐️',
                                5 => '⭐️⭐️⭐️⭐️⭐️',
                            ])
                            ->inline()
                            ->required(),
                    ]),
                Forms\Components\Textarea::make('comment')
                    ->label(__('message.Comment'))
                    ->columnSpanFull(),
                Forms\Components\Select::make('user_id')
                    ->label(__('message.User'))
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn(User $user) => "{$user->name} [{$user->email}]")
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'email',
                        modifyQueryUsing: fn(Builder $query) => $query->where('user_type', UserType::USER)
                    )
                    ->required(),
                Forms\Components\Select::make('product_id')
                    ->label(__('message.Product'))
                    ->searchable()
                    ->preload()
                    ->relationship('product', app()->getLocale() == 'ar' ? 'name_ar' : 'name_en')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rating')
                    ->formatStateUsing(fn(int $state): string => str_repeat('⭐️', $state)),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('message.User'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make(app()->getLocale() == 'ar' ? 'product.name_ar' : 'product.name_en')
                    ->label(__('message.Product'))
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
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('product')
                    ->relationship('product', app()->getLocale() == 'ar' ? 'name_ar' : 'name_en')
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
