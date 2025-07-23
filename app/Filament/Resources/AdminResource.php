<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserType;

class AdminResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('message.Admins');
    }

    public static function getNavigationGroup(): string
    {
        return __('message.User Types');
    }

    protected static ?string $slug = 'admins';
    protected static ?string $recordTitleAttribute = 'name';

    public static function getPluralModelLabel(): string
    {
        return __('message.Admins');
    }

    public static function getModelLabel(): string
    {
        return __('message.Admin');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_type', 'admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('message.Name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label(__('message.Email'))
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label(__('message.Phone'))
                    ->unique(ignoreRecord: true)
                    ->regex('/^01[0-5]\d{8}$/')
                    ->minLength(11)
                    ->tel()
                    ->required()
                    ->rules(['regex:/^01[0-5]\d{8}$/'])
                    ->validationAttribute(__('message.Phone')),
                Forms\Components\FileUpload::make('image')
                    ->label(__('message.Image'))
                    ->directory('admins')
                    ->default('assets/img/default.png')
                    ->image(),
                Forms\Components\Hidden::make('user_type')
                    ->default('admin')
                    ->dehydrated(true),
                Forms\Components\Select::make('status')
                    ->label(__('message.Status'))
                    ->required()
                    ->default('active')
                    ->options([
                        'active' => __('message.Active'),
                        'inactive' => __('message.Inactive'),
                    ]),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->dehydrated(fn($state) => filled($state))
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->maxLength(255)
                    ->regex('/^(?=.*[A-Za-z])(?=.*\d).+$/')
                    ->label(__('message.Password'))
                    ->revealable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('message.Image'))
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(asset('assets/img/default.png')),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('message.Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('message.Email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('message.Phone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_type')
                    ->label(__('message.User Type'))
                    ->badge()
                    ->color(fn($state) => $state == UserType::ADMIN ? 'primary' : 'info')
                    ->formatStateUsing(fn($state) => $state == UserType::ADMIN ? __('message.Admin') : __('message.User')),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('message.Status'))
                    ->badge()
                    ->color(fn($state) => $state == 'active' ? 'success' : 'danger')
                    ->formatStateUsing(fn($state) => $state == 'active' ? __('message.Active') : __('message.Inactive')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('message.Created At'))
                    ->dateTime('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('message.Updated At'))
                    ->dateTime('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('message.Status'))
                    ->options([
                        'active' => __('message.Active'),
                        'inactive' => __('message.Inactive'),
                    ])
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
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }
}
