<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Filament\Resources\NotificationResource\RelationManagers;
use App\Models\Notification;
use App\Repository\Contracts\NotificationRepositoryInterface;
use App\Services\Implementations\FirebaseService;
use App\Services\Implementations\NotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    public static ?int $navigationSort = 9;
    public static function getNavigationGroup(): string
    {
        return __('message.Store Management');
    }
    public static function getNavigationLabel(): string
    {
        return __('message.Notifications');
    }

    public static function getModelLabel(): string
    {
        return __('message.Notification');
    }

    public static function getPluralModelLabel(): string
    {
        return __('message.Notifications');
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('type', 'topic')
            ->where('to', 'user');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__('message.Title'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('to')
                    ->label(__('message.To'))
                    ->options([
                        'user' => __('message.Users Login'),
                        'all' => __('message.All'),
                    ])
                    ->required(),
                Forms\Components\Textarea::make('body')
                    ->label(__('message.Body'))
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->label(__('message.Image'))
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('type')
                    ->default('topic'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('message.Title'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('body')
                    ->label(__('message.Body'))
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('message.Image'))
                    ->defaultImageUrl('https://cdn-icons-png.flaticon.com/128/739/739249.png'),
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
                Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}
