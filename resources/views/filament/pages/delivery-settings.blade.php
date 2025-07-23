<x-filament-panels::page>
    {{ $this->form }}

    <x-filament::button wire:click="save" type="submit">
        {{ __('message.Edit') }}
    </x-filament::button>
</x-filament-panels::page>