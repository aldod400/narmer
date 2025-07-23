<?php

namespace App\Filament\Resources\CouponResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DetachBulkAction;
use Illuminate\Database\Eloquent\Model;

class UserCouponsRelationManager extends RelationManager
{
    protected static string $relationship = 'users';
    protected static ?string $recordTitleAttribute = 'email';
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('message.Users');
    }
    public static function getNavigationLabel(): string
    {
        return __('message.Users');
    }

    public static function getModelLabel(): string
    {
        return __('message.User');
    }

    public static function getPluralModelLabel(): string
    {
        return __('message.Users');
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('used_at')
                    ->label(__('message.Used At'))
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->label(__('message.Email')),
                Tables\Columns\TextColumn::make('pivot.used_at')
                    ->label(__('message.Used At')),
            ])
            ->headerActions([
                Action::make('attachUser')
                    ->label(__('message.Add User'))
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\Select::make('user_id')
                            ->label(__('message.User'))
                            ->options(User::pluck('email', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\DateTimePicker::make('used_at')
                            ->label(__('message.Used At'))
                            ->default(now())
                            ->required(),
                    ]))
                    ->action(function (array $data) {
                        $coupon = $this->getOwnerRecord();
                        $userId = $data['user_id'];

                        if ($coupon->users()->where('user_id', $userId)->exists()) {
                            Notification::make()
                                ->danger()
                                ->title(__('message.User already attached'))
                                ->send();

                            return;
                        }

                        if (
                            $coupon->usage_limit !== null &&
                            $coupon->users()->count() >= $coupon->usage_limit
                        ) {
                            Notification::make()
                                ->danger()
                                ->title(__('message.Coupon usage limit reached'))
                                ->send();

                            return;
                        }

                        $coupon->users()->attach($userId, [
                            'used_at' => $data['used_at'],
                        ]);

                        Notification::make()
                            ->success()
                            ->title(__('message.User attached successfully'))
                            ->send();
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->label(__('message.Edit Used At'))
                    ->form(fn(Form $form) => $this->form($form)),
                DetachAction::make()
                    ->label(__('message.Detach User')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label(__('message.Detach Selected')),
                ]),
            ]);
    }
}
