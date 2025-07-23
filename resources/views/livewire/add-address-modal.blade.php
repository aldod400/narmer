<div>
    <!-- Debug information (visible only in development) -->
    @if(config('app.debug'))
        <div class="hidden">
            Modal Status: {{ $open ? 'Open' : 'Closed' }} | User ID: {{ $userId ?? 'None' }}
        </div>
    @endif

    <!-- Modal -->
    <div x-data="{ open: @entangle('open') }" x-init="
            $wire.on('refreshModal', () => { open = $wire.open });
            console.log('Modal initialized, open state:', open);
         " x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"></div>

            <!-- Modal Content -->
            <div x-show="open" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6 mx-auto"
                @click.outside="$wire.closeModal()">
                {{-- <div class="absolute top-3 right-3">
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div> --}}

                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-5">
                    {{ __('message.Add New Address') }}
                </h3>

                <form wire:submit="save">
                    {{ $this->form }}

                    <div class="mt-6 flex justify-end">
                        <button type="button" wire:click="closeModal"
                            class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600">
                            {{ __('message.Cancel') }}
                        </button>
                        &nbsp;
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600">
                            <span wire:loading.remove wire:target="save">
                                {{ __('message.Save Address') }}
                            </span>
                            <span wire:loading wire:target="save">
                                {{ __('message.Saving...') }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>