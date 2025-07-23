<?php

namespace App\Filament\Pages\Widgets;

use App\Models\Address;
use App\Models\City;
use App\Models\Area;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;

class AddAddressModal extends Component implements HasForms
{
    use InteractsWithForms;

    public $open = false;
    public $userId = null;
    public $address = [];

    public function mount($userId = null)
    {
        $this->userId = $userId;
        $this->form->fill();
    }

    #[On('open-add-address')]
    public function openModal($data = null)
    {
        if (is_array($data) && isset($data['userId'])) {
            $this->userId = $data['userId'];
        } elseif (is_numeric($data)) {
            $this->userId = $data;
        }
        $this->reset('address');
        $this->form->fill();

        $this->open = true;

        $this->dispatch('open-modal');

        $this->dispatch('refreshModal');
    }

    public function openModalDirect($userId)
    {
        $this->userId = $userId;
        $this->reset('address');
        $this->form->fill();
        $this->open = true;
    }

    public function closeModal()
    {
        $this->open = false;
        $this->reset('address');
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')
                    ->default(fn() => $this->userId),

                Select::make('name')
                    ->label(__('message.Address Name'))
                    ->options([
                        'home' => __('message.Home'),
                        'work' => __('message.Work'),
                        'other' => __('message.Other'),
                    ])
                    ->default('home')
                    ->required(),

                TextInput::make('address')
                    ->label(__('message.Address'))
                    ->placeholder(__('message.Street, Building, Apartment, etc.'))
                    ->required(),
                TextInput::make('phone')
                    ->label(__('message.Phone Number'))
                    ->tel()
                    ->required(),
                Select::make('city_id')
                    ->label(__('message.City'))
                    ->options(City::pluck(app()->getLocale() == 'ar' ? 'name_ar' : 'name_en', 'id'))
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn(callable $set) => $set('area_id', null)),
                Map::make('location')
                    ->label(__('message.Select Location'))
                    ->draggable()
                    ->defaultZoom(15)
                    ->defaultLocation([
                        31.1840033388827,
                        29.91577952653098
                    ])
                    ->clickable()
                    ->autocomplete('address')
                    ->autocompleteReverse()
                    ->reverseGeocode([
                        'city' => '%L',
                        'address' => '%n %S',
                    ])
                    ->mapControls([
                        'mapTypeControl' => true,
                        'scaleControl' => true,
                        'streetViewControl' => true,
                        'rotateControl' => true,
                        'fullscreenControl' => true,
                        'searchBoxControl' => true,
                        'zoomControl' => true,
                    ])
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (is_array($state) && isset($state['lat'], $state['lng'])) {
                            $set('lat', $state['lat']);
                            $set('lng', $state['lng']);
                        }
                    }),

                TextInput::make('lat')
                    ->label(__('message.Latitude'))
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('lng')
                    ->label(__('message.Longitude'))
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),
                Toggle::make('is_default')
                    ->label(__('message.Set as Default'))
                    ->default(false),
            ])
            ->statePath('address');
    }

    public function save()
    {
        try {
            // Validate the form data
            $data = $this->form->getState();

            // Ensure we have user_id
            if (!$this->userId) {
                Notification::make()
                    ->title(__('message.Error'))
                    ->body(__('message.Please select a customer first'))
                    ->danger()
                    ->send();
                return;
            }

            // Validate required fields
            if (empty($data['name']) || empty($data['address']) || empty($data['phone'])) {
                Notification::make()
                    ->title(__('message.Error'))
                    ->body(__('message.Please fill in all required fields'))
                    ->danger()
                    ->send();
                return;
            }

            // Set default values for lat/lng if not provided
            if (!isset($data['lat']) || !$data['lat']) {
                $data['lat'] = 0;
            }

            if (!isset($data['lng']) || !$data['lng']) {
                $data['lng'] = 0;
            }

            // Explicitly set the user_id
            $data['user_id'] = $this->userId;

            // Use database transaction to ensure data consistency
            DB::beginTransaction();

            try {
                // If this is set as default, unset any other default addresses
                if (isset($data['is_default']) && $data['is_default']) {
                    Address::where('user_id', $this->userId)
                        ->where('is_default', true)
                        ->update(['is_default' => false]);
                }

                // Create the address
                $address = Address::create($data);

                DB::commit();

                Notification::make()
                    ->title(__('message.Address Added'))
                    ->success()
                    ->send();

                $this->closeModal();

                // Dispatch event to refresh addresses in parent component
                $this->dispatch('address-added', $address->id);
            } catch (\Exception $innerException) {
                // Roll back the transaction if anything went wrong
                DB::rollBack();
                throw $innerException;
            }
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error("Error saving address: " . $e->getMessage(), [
                'exception' => $e,
                'userId' => $this->userId,
                'data' => $data ?? null
            ]);

            Notification::make()
                ->title(__('message.Error'))
                ->body(__('message.Failed to save address: ') . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        try {
            return view('livewire.add-address-modal');
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error("Error rendering address modal: " . $e->getMessage(), [
                'exception' => $e,
                'userId' => $this->userId
            ]);

            // Handle the error gracefully
            Notification::make()
                ->title(__('message.Error'))
                ->body(__('message.Error rendering address form. Please try again.'))
                ->danger()
                ->send();

            return view('livewire.error-message', [
                'message' => __('message.Error rendering address form. Please try again.')
            ]);
        }
    }
}
