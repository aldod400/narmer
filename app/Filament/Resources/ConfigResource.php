<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConfigResource\Pages;
use App\Filament\Resources\ConfigResource\RelationManagers;
use App\Models\Config;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConfigResource extends Resource
{
    protected static ?string $model = Config::class;
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    public static function getModelLabel(): string
    {
        return __('message.Config');
    }

    public static function getPluralModelLabel(): string
    {
        return __('message.Configs');
    }
    public static function getNavigationGroup(): string
    {
        return __('message.Settings');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('android_app_version')
                    ->label(__('message.Android App Version'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('ios_app_version')
                    ->label(__('message.IOS App Version'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('android_app_url')
                    ->label(__('message.Android App URL'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ios_app_url')
                    ->label(__('message.IOS App URL'))
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('terms_and_conditions')
                    ->label(__('message.Terms and Conditions'))
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('privacy_policy')
                    ->label(__('message.Privacy Policy'))
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('refund_policy')
                    ->label(__('message.Refund Policy'))
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('about_us')
                    ->label(__('message.About Us'))
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('contact_us')
                    ->label(__('message.Contact Us'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('facebook')
                    ->label(__('message.Facebook'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('twitter')
                    ->label(__('message.Twitter'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('instagram')
                    ->label(__('message.Instagram'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('linkedin')
                    ->label(__('message.Linkedin'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('tiktok')
                    ->label(__('message.Tiktok'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('whatsapp')
                    ->label(__('message.Whatsapp'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('android_app_version')
                    ->label(__('message.Android App Version'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ios_app_version')
                    ->label(__('message.IOS App Version'))
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
                    ->toggleable(true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(__('message.Edit')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('message.Delete')),
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
            'index' => Pages\ListConfigs::route('/'),
            'create' => Pages\CreateConfig::route('/create'),
            'edit' => Pages\EditConfig::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }
}
