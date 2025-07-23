<x-filament-panels::page>
    <div x-data="{ debug: false }" @open-add-address.window="
            console.log('Event received:', $event.detail);
            // Directly dispatch to the Livewire component
            if ($event.detail && $event.detail.userId) {
                $wire.dispatch('open-add-address', { userId: $event.detail.userId });
                
                // Alternative method if the above doesn't work
                Livewire.dispatch('open-add-address', { userId: $event.detail.userId });
            }
        " @refresh-form.window="setTimeout(() => { window.dispatchEvent(new Event('resize')); }, 200);">
        <!-- Set a unique key to ensure the component properly reinitializes -->
        @livewire('add-address-modal', ['userId' => $selectedUser?->id, 'key' => 'address-modal-' . ($selectedUser?->id ?? 'no-user') . '-' . now()->timestamp])

        <!-- Product Attributes Modal -->
        <div x-data="{ 
                open: @entangle('showAttributeModal'), 
                calculatedPrice: 0,
                selectedAttrs: @entangle('selectedAttributes'),
                updateCalculatedPrice() {
                    this.$wire.calculateAttributePrice().then(result => {
                        this.calculatedPrice = result;
                    });
                }
            }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-init="$watch('open', value => {
                if(!value) return;
                calculatedPrice = 0;
            });
            $watch('selectedAttrs', value => {
                updateCalculatedPrice();
            }, { deep: true });">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6 mx-auto transform transition-all"
                    x-show="open" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    @click.outside="open = false">
                    {{-- <div class="absolute top-3 right-3">
                        <button type="button" @click="open = false"
                            class="text-gray-400 hover:text-gray-500 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-full p-1">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div> --}}

                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                        {{ __('message.Select Attributes') }}
                    </h3>

                    @if($selectedProduct)
                        <div class="mb-4 flex items-center">
                            @if($selectedProduct->images->count() > 0)
                                <img src="{{ env('APP_URL') . '/storage/' . $selectedProduct->images->first()->image }}"
                                    class="w-16 h-16 object-cover rounded-md mr-3"
                                    alt="{{ app()->getLocale() == 'ar' ? $selectedProduct->name_ar : $selectedProduct->name_en }}">
                            @else
                                <div
                                    class="w-full h-40 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-md overflow-hidden mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">
                                    {{ app()->getLocale() == 'ar' ? $selectedProduct->name_ar : $selectedProduct->name_en }}
                                </h4>
                                <div class="flex items-center text-sm mt-1">
                                    @if($selectedProduct->discount_price)
                                        <span class="text-primary-600 dark:text-primary-400 font-bold">
                                            {{ number_format($selectedProduct->discount_price, 2) }}
                                            {{ __('message.currency') }}
                                        </span>
                                        <span class="text-xs text-gray-500 line-through ml-2">
                                            {{ number_format($selectedProduct->price, 2) }} {{ __('message.currency') }}
                                        </span>
                                    @else
                                        <span class="text-primary-600 dark:text-primary-400 font-bold">
                                            {{ number_format($selectedProduct->price, 2) }} {{ __('message.currency') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @foreach($productAttributes as $attribute)
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $attribute['name'] }}</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($attribute['values'] as $value)
                                            <div>
                                                <input type="radio" id="attr_{{ $attribute['id'] }}_{{ $value['id'] }}"
                                                    name="attr_{{ $attribute['id'] }}"
                                                    wire:model="selectedAttributes.{{ $attribute['id'] }}"
                                                    value="{{ $value['id'] }}" class="hidden peer" wire:change="$refresh">
                                                <label for="attr_{{ $attribute['id'] }}_{{ $value['id'] }}"
                                                    class="flex items-center justify-between w-full p-3 text-gray-700 bg-white border border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-600 dark:peer-checked:border-primary-500 dark:peer-checked:text-primary-600 peer-checked:border-primary-500 peer-checked:bg-primary-50 dark:peer-checked:bg-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600"
                                                    @if($selectedAttributes[$attribute['id']] == $value['id'])
                                                        style="border-color: rgb(79 70 229); background-color: rgb(238 242 255); color: rgb(79 70 229); font-weight: bold;"
                                                    @endif>
                                                    <div class="block">
                                                        <div class="font-semibold">{{ $value['value'] }}</div>
                                                    </div>
                                                    @if($value['price'] > 0)
                                                        <div class="text-xs text-primary-600 dark:text-primary-400">
                                                            +{{ number_format($value['price'], 2) }} {{ __('message.currency') }}
                                                        </div>
                                                    @endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div
                            class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4 flex justify-between items-center">

                            <div class="flex space-x-3">
                                <button type="button" wire:click="$set('showAttributeModal', false)"
                                    class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 transition-colors">
                                    {{ __('message.Cancel') }}
                                </button>

                                &nbsp;

                                <button type="button" wire:click="addToCartWithAttributes({{ $selectedProduct->id }})"
                                    wire:loading.attr="disabled" wire:loading.class="opacity-75"
                                    class="px-4 py-2 text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 transition-colors flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" wire:loading>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    {{ __('message.Add to Cart') }}
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>


        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Side - Customer Info & Cart -->
            <div class="md:col-span-1 space-y-6">
                <form wire:submit.prevent="create">
                    {{ $this->form }}
                    <br>
                    <!-- Cart Section -->
                    <div class="bg-white rounded-xl shadow p-4 dark:bg-gray-800">
                        <div class="flex justify-between items-center mb-3">
                            <h2 class="text-lg font-bold">{{ __('message.Cart') }}</h2>
                            <span class="text-sm font-medium">{{ count($cart) }} {{ __('message.items') }}</span>
                        </div>

                        @if(count($cart) > 0)
                            <div class="space-y-3 max-h-[300px] overflow-y-auto">
                                @foreach($cart as $productId => $item)
                                    <div
                                        class="flex justify-between pb-3 {{ !$loop->last ? 'border-b dark:border-gray-700' : '' }}">
                                        <div class="flex-1">
                                            <p class="font-medium text-sm">{{ $item['name'] }}</p>
                                            @if(!empty($item['attributes']))
                                                <div class="mt-1 text-xs">
                                                    @foreach($item['attributes'] as $attr)
                                                        <div class="flex items-center space-x-1 text-gray-500 dark:text-gray-400">
                                                            <span class="font-medium">{{ $attr['attribute_name'] }}:</span>
                                                            <span>{{ $attr['value'] }}</span>
                                                            @if($attr['price'] > 0)
                                                                <span
                                                                    class="text-primary-500 dark:text-primary-400">(+{{ number_format($attr['price'], 2) }})</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <div class="flex items-center mt-1">
                                                <button type="button"
                                                    wire:click="updateQuantity('{{ $productId }}', 'decrease')"
                                                    class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 h-6 w-6 rounded flex items-center justify-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M20 12H4" />
                                                    </svg>
                                                </button>
                                                <span class="mx-2 text-sm w-8 text-center">{{ $item['quantity'] }}</span>
                                                <button type="button"
                                                    wire:click="updateQuantity('{{ $productId }}', 'increase')"
                                                    class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 h-6 w-6 rounded flex items-center justify-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 4v16m8-8H4" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end">
                                            <p class="font-semibold text-sm">
                                                {{ number_format($item['price'] * $item['quantity'], 2) }}
                                                {{ __('message.currency') }}
                                            </p>
                                            <button type="button" wire:click="removeFromCart('{{ $productId }}')"
                                                style="
                                                                                                                                        background-color: #dc3545;
                                                                                                                                        color: white;
                                                                                                                                        padding: 10px 20px;
                                                                                                                                        font-size: 12px;
                                                                                                                                        font-weight: 600;
                                                                                                                                        border: none;
                                                                                                                                        border-radius: 10px;
                                                                                                                                        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
                                                                                                                                        transition: background-color 0.2s ease;
                                                                                                                                        margin-bottom: 10px;
                                                                                                                                    " onmouseover="this.style.backgroundColor='#bb2d3b'"
                                                onmouseout="this.style.backgroundColor='#dc3545'">
                                                {{ __('message.Remove') }}
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-6 flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <p>{{ __('message.Cart is empty') }}</p>
                                <p class="text-xs mt-1">{{ __('message.Add products to start the order') }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-6">
                        <button type="submit"
                            class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-sm flex items-center justify-center transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
                            @if(empty($cart) || !$selectedUser) disabled @endif>
                            {{ __('message.Create Order') }}

                            <svg class="h-5 w-5 ml-1.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path
                                    d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                            </svg>

                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Side - Product Selection -->
            <div class="md:col-span-2 bg-white rounded-xl shadow p-4 dark:bg-gray-800">
                <!-- Search and Filter -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <input type="text" wire:model.live.debounce.300ms="productSearch"
                            placeholder="{{ __('message.Search products...') }}"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm placeholder-gray-400 shadow-sm focus:border-primary-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400" />
                    </div>

                    <div class="md:col-span-1">
                        <select wire:model.live="categoryFilter"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm placeholder-gray-400 shadow-sm focus:border-primary-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">{{ __('message.All Categories') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ app()->getLocale() == 'ar' ? $category->name_ar : $category->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>


                <br>


                <!-- Products Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 overflow-y-auto"
                    style="max-height: 170vh;">
                    @forelse($products as $product)
                        <div
                            class="border dark:border-gray-700 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <div class="relative h-32 bg-gray-100 dark:bg-gray-700">
                                @if($product->images->count() > 0)



                                    <img src="{{ env('APP_URL') . '/storage/' . $product->images->first()->image }}"
                                        class="w-full h-full object-cover transition-transform duration-300 hover:scale-110 rounded-t-xl"
                                        alt="{{ app()->getLocale() == 'ar' ? $product->name_ar : $product->name_en }}"
                                        loading="lazy"
                                        onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTQgMTZsNC41ODYtNC41ODZhMiAyIDAgMDEyLjgyOCAwTDE2IDE2bS0yLTJsMS41ODYtMS41ODZhMiAyIDAgMDEyLjgyOCAwTDIwIDE0bS02LTZoLjAxTTYgMjBoMTJhMiAyIDAgMDAyLTJWNmEyIDIgMCAwMC0yLTJINmEyIDIgMCAwMC0yIDJ2MTJhMiAyIDAgMDAyIDJ6IiBzdHJva2U9IiM5Q0E3QjEiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+Cjwvc3ZnPgo='; this.classList.add('p-8', 'bg-gray-100', 'dark:bg-gray-700');">


                                @else
                                    <div class="flex items-center justify-center h-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif


                            </div>

                            <div class="p-3">
                                <h3 class="font-medium text-sm mb-1 truncate">
                                    {{ app()->getLocale() == 'ar' ? $product->name_ar : $product->name_en }}
                                </h3>

                                <div class="flex justify-between items-center mb-3">
                                    <div>
                                        @if($product->discount_price)
                                            <span
                                                class="text-primary-600 dark:text-primary-400 font-bold">{{ number_format($product->discount_price, 2) }}</span>
                                            <span
                                                class="text-xs text-gray-500 line-through ml-1">{{ number_format($product->price, 2) }}</span>
                                        @else
                                            <span
                                                class="text-primary-600 dark:text-primary-400 font-bold">{{ number_format($product->price, 2) }}</span>
                                        @endif
                                        <span class="text-xs">{{ __('message.currency') }}</span>
                                    </div>

                                </div>

                                <span class="text-xs text-gray-500">
                                    {{ __('message.Stock') }}: {{ $product->quantity }}
                                </span>

                                <button type="button" wire:click="addToCart({{ $product->id }})"
                                    wire:loading.attr="disabled" wire:loading.class="opacity-75"
                                    class="w-full bg-primary-500 hover:bg-primary-600 text-white text-sm font-medium py-1.5 rounded-lg transition-colors">
                                    {{ __('message.Add to Cart') }}
                                </button>
                            </div>
                        </div>
                    @empty
                        <div
                            class="col-span-full flex flex-col items-center justify-center py-12 text-gray-500 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <p class="text-lg">{{ __('message.No products found') }}</p>
                            <p class="text-sm mt-1">{{ __('message.Try a different search term or category') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
</x-filament-panels::page>