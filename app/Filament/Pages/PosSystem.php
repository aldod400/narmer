<?php

namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Enums\UserType;
use App\Helpers\DestanceHelpers;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Implementations\DeliveryFeeService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Support\Enums\IconPosition;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\Area;
use App\Models\City;
use App\Models\Setting;
use App\Repository\Contracts\AddressRepositoryInterface;
use App\Repository\Contracts\CityRepositoryInterface;
use App\Services\Implementations\FirebaseService;
use App\Repository\Contracts\NotificationRepositoryInterface;
use App\Repository\Contracts\SettingRepositoryInterface;

class PosSystem extends Page implements HasForms
{
    use InteractsWithForms, WithPagination;

    protected $firebaseService;

    protected static string $view = 'filament.pages.pos-system';
    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $navigationGroup = 'POS Management';
    protected static ?string $navigationLabel = 'POS System';
    protected static ?string $title = 'Point of Sale';
    protected static ?string $slug = 'pos-system';
    protected static ?int $navigationSort = 1;
    public function getTitle(): string
    {
        return __('message.POS System');
    }
    public static function getNavigationLabel(): string
    {
        return __('message.POS System');
    }

    public static function getNavigationGroup(): string
    {
        return __('message.POS System');
    }

    public static function getPluralModelLabel(): string
    {
        return __('message.POS System');
    }

    public static function getModelLabel(): string
    {
        return __('message.POS System');
    }
    public ?array $data = [];

    public $selectedUser = null;
    public $selectedAddress = null;
    public $addresses = [];
    public $products = [];
    public $cart = [];
    public $subtotal = 0;
    public $deliveryFee = 0;
    public $total = 0;
    public $discount = 0;
    public $coupon = null;
    public $productSearch = '';
    public $categoryFilter = null;
    public $categories = [];
    public $selectedProduct = null;
    public $showAttributeModal = false;
    public $productAttributes = [];
    public $selectedAttributes = [];

    public function mount(): void
    {
        $this->form->fill();
        $this->loadProducts();

        // Initialize cart
        $this->cart = [];

        // Load categories for filtering
        $this->categories = \App\Models\Category::all();
    }

    public function loadProducts()
    {
        $query = Product::where('status', ProductStatus::ACTIVE->value)
            ->where('quantity', '>', 0);

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        if ($this->productSearch) {
            $query->where(function ($q) {
                $q->where('name_en', 'like', "%{$this->productSearch}%")
                    ->orWhere('name_ar', 'like', "%{$this->productSearch}%");
            });
        }

        $this->products = $query->get();
    }

    public function updatedProductSearch()
    {
        $this->loadProducts();
    }

    public function updatedCategoryFilter()
    {
        $this->loadProducts();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('message.Customer Information'))
                    ->description(__('message.Search and select a customer for this order'))
                    ->schema([
                        Select::make('user_id')
                            ->label(__('message.Customer'))
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(
                                fn(string $search): array =>
                                User::where('user_type', UserType::USER)
                                    ->where(function ($query) use ($search) {
                                        return $query->where('name', 'like', "%{$search}%")
                                            ->orWhere('email', 'like', "%{$search}%")
                                            ->orWhere('phone', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($user) {
                                        return [$user->id => "{$user->name} - {$user->phone}"];
                                    })->toArray()
                            )
                            ->afterStateUpdated(function ($state) {
                                if ($state) {
                                    $this->selectedUser = User::find($state);
                                    $this->addresses = Address::where('user_id', $state)->get();
                                    $this->selectedAddress = null;

                                    // Force re-render to show addresses immediately
                                    $this->dispatch('user-selected');
                                } else {
                                    $this->selectedUser = null;
                                    $this->addresses = [];
                                    $this->selectedAddress = null;
                                }
                            })
                            ->required()
                            ->live(),

                        Select::make('address_id')
                            ->label(__('message.Address'))
                            ->options(fn() => $this->addresses?->pluck('address', 'id') ?? [])
                            ->searchable()
                            ->afterStateUpdated(function ($state) {
                                $this->selectedAddress = $state ? Address::find($state) : null;
                            })
                            ->disabled(fn() => !$this->selectedUser)
                            ->required()
                            ->visible(fn() => $this->selectedUser)
                            ->reactive()
                            ->suffixAction(function () {
                                if ($this->selectedUser) {
                                    return \Filament\Forms\Components\Actions\Action::make('addNewAddress')
                                        ->icon('heroicon-m-plus')
                                        ->label(__('message.New'))
                                        ->extraAttributes(['class' => 'ml-2'])
                                        ->action(function () {
                                            if ($this->selectedUser && $this->selectedUser->id) {
                                                $this->dispatch('open-add-address', ['userId' => $this->selectedUser->id]);
                                            } else {

                                                Notification::make()
                                                    ->title(__('message.Error'))
                                                    ->body(__('message.Please select a customer first'))
                                                    ->danger()
                                                    ->send();
                                            }
                                        });
                                }
                                return null;
                            }),
                        Select::make('city_id')
                            ->label(__('message.City'))
                            ->options(City::pluck(app()->getLocale() == 'ar' ? 'name_ar' : 'name_en', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('area_id', null))
                            ->required(fn() => Setting::where('key', 'delivery_fee_type')->value('value') === 'area')
                            ->visible(fn() => Setting::where('key', 'delivery_fee_type')->value('value') === 'area'),
                        Select::make('area_id')
                            ->label(__('message.Area'))
                            ->options(function (callable $get) {
                                $cityId = $get('city_id');
                                if (!$cityId) {
                                    return [];
                                }
                                return Area::where('city_id', $cityId)
                                    ->pluck(app()->getLocale() == 'ar' ? 'name_ar' : 'name_en', 'id');
                            })
                            ->searchable()
                            ->required(fn() => Setting::where('key', 'delivery_fee_type')->value('value') === 'area')
                            ->visible(fn() => Setting::where('key', 'delivery_fee_type')->value('value') === 'area'),

                    ]),

                Section::make(__('message.Order Summary'))
                    ->schema([
                        Select::make('coupon_id')
                            ->label(__('message.Apply Coupon (Optional)'))
                            ->searchable()
                            ->options(Coupon::whereDate('expiry_date', '>=', now())
                                ->pluck('code', 'id'))
                            ->afterStateUpdated(function ($state) {
                                if ($state) {
                                    if (!$this->selectedUser?->id) {
                                        Notification::make()
                                            ->title(__('message.Error'))
                                            ->body(__('message.Please select a customer first'))
                                            ->danger()
                                            ->send();
                                        return;
                                    }
                                    $coupon = Coupon::find($state);
                                    $usersCoupon = $coupon->users();
                                    if (!$coupon) {
                                        Notification::make()
                                            ->title(__('message.Error'))
                                            ->body(__('message.Invalid coupon selected'))
                                            ->danger()
                                            ->send();
                                        return;
                                    }
                                    // Check if coupon is still valid
                                    if ($coupon->expiry_date && $coupon->expiry_date < now()) {
                                        Notification::make()
                                            ->title(__('message.Error'))
                                            ->body(__('message.This coupon has expired'))
                                            ->danger()
                                            ->send();
                                        return;
                                    }
                                    if ($usersCoupon->count() >= $coupon->usage_limit) {
                                        Notification::make()
                                            ->title(__('message.Error'))
                                            ->body(__('message.Coupon usage limit reached'))
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    $userCoupon = DB::table('user_coupons')->where('user_id', $this->selectedUser->id)->first();

                                    if ($userCoupon?->user_id == $this->selectedUser->id) {
                                        Notification::make()
                                            ->title(__('message.Error'))
                                            ->body(__('message.This user has already used this coupon.'))
                                            ->danger()
                                            ->send();
                                        return;
                                    }


                                    $this->coupon = $coupon;
                                    $this->calculateTotal();

                                    $discountType = $this->coupon->discount_type;
                                    if (is_object($discountType)) {
                                        $discountType = $discountType->value;
                                    }

                                    $discountText = $discountType === 'percentage'
                                        ? $this->coupon->discount_value . '%'
                                        : number_format($this->coupon->discount_value, 2) . ' ' . __('message.currency');

                                    Notification::make()
                                        ->title(__('message.Coupon Applied'))
                                        ->body(__('message.Discount of') . ' ' . $discountText . ' ' . __('message.has been applied'))
                                        ->success()
                                        ->send();
                                } else {
                                    $this->coupon = null;
                                    $this->calculateTotal();

                                    Notification::make()
                                        ->title(__('message.Coupon Removed'))
                                        ->info()
                                        ->send();
                                }
                            })
                            ->live(),

                        Placeholder::make('subtotal_display')
                            ->label(__('message.Subtotal'))
                            ->content(fn(): string => number_format($this->subtotal, 2) . ' ' . __('message.currency')),

                        Placeholder::make('discount_display')
                            ->label(__('message.Discount'))
                            ->content(function (): string {
                                if ($this->coupon && $this->discount > 0) {
                                    $discountType = $this->coupon->discount_type;
                                    if (is_object($discountType)) {
                                        $discountType = $discountType->value;
                                    }

                                    $discountInfo = $discountType === 'percentage'
                                        ? "({$this->coupon->discount_value}%)"
                                        : "(Fixed: " . number_format($this->coupon->discount_value, 2) . " " . __('message.currency') . ")";

                                    return number_format($this->discount, 2) . ' ' . __('message.currency') .
                                        ' - ' . $this->coupon->code . ' ' . $discountInfo;
                                }
                                return number_format($this->discount, 2) . ' ' . __('message.currency');
                            }),
                        Placeholder::make('deliveryfee_display')
                            ->label(__('message.Delivery Fee'))
                            ->content(fn(): string => number_format($this->deliveryFee, 2) . ' ' . __('message.currency'))
                            ->visible(fn() => Setting::where('key', 'deliveryman')->value('value')),
                        Placeholder::make('total_display')
                            ->label(__('message.Total'))
                            ->content(fn(): string => number_format($this->total, 2) . ' ' . __('message.currency')),


                        TextInput::make('notes')
                            ->label(__('message.Notes'))
                            ->placeholder(__('message.Any special instructions or notes for this order'))
                            ->maxLength(255),
                    ]),
            ])
            ->statePath('data');
    }
    public function showProductAttributes($productId)
    {
        try {
            $product = Product::with(['images'])->find($productId);
            if (!$product || $product->quantity <= 0) {
                Notification::make()
                    ->title(__('message.Product out of stock'))
                    ->warning()
                    ->send();
                return;
            }

            $this->selectedProduct = $product;
            $this->productAttributes = [];
            $this->selectedAttributes = [];

            // Get product attribute values directly through the pivot table
            $query = "
                SELECT 
                    pav.id as pav_id,
                    pav.product_id,
                    av.attribute_id,
                    pav.attribute_value_id,
                    pav.price,
                    av.value,
                    a.name_en,
                    a.name_ar
                FROM 
                    product_attribute_values pav
                JOIN 
                    attribute_values av ON pav.attribute_value_id = av.id
                JOIN 
                    attributes a ON av.attribute_id = a.id
                WHERE 
                    pav.product_id = ?
            ";

            $productAttributeValues = DB::select($query, [$productId]);

            if (empty($productAttributeValues)) {
                // If no attributes, add product directly
                $this->addToCartWithAttributes($product->id);
                return;
            }

            // Group by attribute
            $attributesMap = [];
            foreach ($productAttributeValues as $row) {
                $attrId = $row->attribute_id;

                if (!isset($attributesMap[$attrId])) {
                    $attributesMap[$attrId] = [
                        'id' => $attrId,
                        'name' => app()->getLocale() == 'ar' ? $row->name_ar : $row->name_en,
                        'values' => []
                    ];
                }

                $attributesMap[$attrId]['values'][] = [
                    'id' => $row->attribute_value_id,
                    'value' => $row->value,
                    'price' => $row->price ?? 0,
                ];
            }

            $this->productAttributes = array_values($attributesMap);

            // Default selection for the first value of each attribute
            foreach ($this->productAttributes as $attribute) {
                if (count($attribute['values']) > 0) {
                    $this->selectedAttributes[$attribute['id']] = $attribute['values'][0]['id'];
                }
            }

            if (empty($this->productAttributes)) {
                // If no attributes found after filtering, add product directly
                $this->addToCartWithAttributes($product->id);
                return;
            }

            $this->showAttributeModal = true;
        } catch (\Exception $e) {
            // Use our error handler
            $this->handleAttributeError($e, 'loading product attributes');

            // Fallback: Add product to cart without attributes
            if (isset($product) && $product) {
                $this->addToCartWithAttributes($product->id);
            }
        }
    }

    public function calculateAttributePrice()
    {
        if (!$this->selectedProduct) {
            return 0;
        }

        $basePrice = $this->selectedProduct->discount_price ?? $this->selectedProduct->price;
        $additionalPrice = 0;

        try {
            if (!empty($this->selectedAttributes)) {
                $selectedAttributeValues = array_values($this->selectedAttributes);

                if (!empty($selectedAttributeValues)) {
                    $placeholders = implode(',', array_fill(0, count($selectedAttributeValues), '?'));

                    // Direct SQL to avoid relationship issues
                    $query = "
                        SELECT attribute_value_id, price
                        FROM product_attribute_values
                        WHERE product_id = ? AND attribute_value_id IN ({$placeholders})
                    ";

                    // Prepare parameters
                    $params = array_merge([$this->selectedProduct->id], $selectedAttributeValues);

                    // Execute query
                    $results = DB::select($query, $params);

                    // Process results
                    $priceMap = [];
                    foreach ($results as $row) {
                        $priceMap[$row->attribute_value_id] = $row->price ?? 0;
                    }

                    foreach ($this->selectedAttributes as $attrId => $valueId) {
                        if (isset($priceMap[$valueId])) {
                            $additionalPrice += $priceMap[$valueId];
                        }
                    }
                }
            }

            return $basePrice + $additionalPrice;
        } catch (\Exception $e) {
            $this->handleAttributeError($e, 'calculating attribute price');
            return $basePrice; // Return base price on error
        }
    }

    public function addToCartWithAttributes($productId, $attributeSelections = [])
    {
        $product = Product::find($productId);
        if (!$product) {
            Notification::make()
                ->title(__('message.Product not found'))
                ->danger()
                ->send();
            return;
        }

        if ($product->quantity <= 0) {
            Notification::make()
                ->title(__('message.Product out of stock'))
                ->warning()
                ->send();
            return;
        }

        // Generate a unique key for the cart item based on product and attributes
        $cartItemKey = $productId;
        $selectedAttributeValues = [];
        $additionalPrice = 0;

        // Determine which attribute selections to use
        $useSelections = !empty($attributeSelections) ? $attributeSelections : $this->selectedAttributes;

        if (!empty($useSelections)) {
            // Process selected attributes
            foreach ($useSelections as $attrId => $valueId) {
                $selectedAttributeValues[] = $valueId;

                // We'll get all price adjustments at once after this loop
            }

            // Make the cart item key unique based on selected attributes
            $cartItemKey .= '_' . implode('_', $selectedAttributeValues);

            // Get all price adjustments at once with direct SQL
            try {
                $placeholders = implode(',', array_fill(0, count($selectedAttributeValues), '?'));

                // Get price adjustments in one query
                $query = "
                    SELECT attribute_value_id, price
                    FROM product_attribute_values
                    WHERE product_id = ? AND attribute_value_id IN ({$placeholders})
                ";

                // Prepare parameters
                $params = array_merge([$product->id], $selectedAttributeValues);

                // Execute query
                $results = DB::select($query, $params);

                // Sum up the additional price
                foreach ($results as $row) {
                    $additionalPrice += floatval($row->price ?? 0);
                }
            } catch (\Exception $e) {
                $this->handleAttributeError($e, 'getting attribute prices');
                // Continue with additionalPrice = 0
            }
        }

        $basePrice = $product->discount_price ?? $product->price;
        $finalPrice = $basePrice + $additionalPrice;

        if (isset($this->cart[$cartItemKey])) {
            // Product with same attributes already exists in cart
            if ($this->cart[$cartItemKey]['quantity'] < $product->quantity) {
                $this->cart[$cartItemKey]['quantity']++;

                Notification::make()
                    ->title(__('message.Product quantity updated'))
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title(__('message.Maximum quantity reached'))
                    ->warning()
                    ->send();
                return;
            }
        } else {
            // Add new product to cart
            $this->cart[$cartItemKey] = [
                'id' => $product->id,
                'name' => app()->getLocale() == 'ar' ? $product->name_ar : $product->name_en,
                'base_price' => $basePrice,
                'price' => $finalPrice,
                'quantity' => 1,
                'max_quantity' => $product->quantity,
                'attributes' => !empty($selectedAttributeValues) ?
                    $this->getSelectedAttributesDetails($productId, $useSelections) : []
            ];

            Notification::make()
                ->title(__('message.Product added to cart'))
                ->success()
                ->send();
        }

        $this->calculateTotal();
        $this->showAttributeModal = false;
        $this->selectedProduct = null;
    }
    protected function getSelectedAttributesDetails($productId, $selectedAttributes)
    {
        if (empty($selectedAttributes)) {
            return [];
        }

        $attributes = [];
        $valueIds = array_values($selectedAttributes);

        try {
            $placeholders = implode(',', array_fill(0, count($valueIds), '?'));

            // Direct SQL query to avoid relationship issues
            $query = "
                SELECT 
                    pav.id as pav_id,
                    pav.product_id,
                    pav.attribute_value_id,
                    pav.price,
                    av.value,
                    av.attribute_id,
                    a.name_en,
                    a.name_ar
                FROM 
                    product_attribute_values pav
                JOIN 
                    attribute_values av ON pav.attribute_value_id = av.id
                JOIN 
                    attributes a ON av.attribute_id = a.id
                WHERE 
                    pav.product_id = ?
                AND
                    pav.attribute_value_id IN ({$placeholders})
            ";

            // Prepare parameters
            $params = array_merge([$productId], $valueIds);

            // Execute query
            $results = DB::select($query, $params);

            // Process results
            foreach ($results as $row) {
                $attributes[] = [
                    'attribute_id' => $row->attribute_id,
                    'attribute_name' => app()->getLocale() == 'ar' ? $row->name_ar : $row->name_en,
                    'value_id' => $row->attribute_value_id,
                    'value' => $row->value,
                    'price' => floatval($row->price ?? 0)
                ];
            }

            return $attributes;
        } catch (\Exception $e) {
            $this->handleAttributeError($e, 'getting attribute details');
            return [];
        }
    }

    #[On('add-to-cart')]
    public function addToCart($productId)
    {
        try {
            if (!$this->selectedAddress) {
                Notification::make()
                    ->title(__('message.Error'))
                    ->body(__('message.Please select an address'))
                    ->danger()
                    ->send();
                return;
            }
            $deliveryType = Setting::where('key', 'delivery_fee_type')->value('value');
            if ($deliveryType === 'area' && !$this->data['area_id']) {
                Notification::make()
                    ->title(__('message.Error'))
                    ->body(__('message.Please select an area'))
                    ->danger()
                    ->send();
                return;
            }

            $this->showProductAttributes($productId);
        } catch (\Exception $e) {
            $this->handleAttributeError($e, 'adding product to cart');

            try {
                $product = Product::find($productId);
                if ($product && $product->quantity > 0) {
                    $this->addToCartWithAttributes($productId, []);
                }
            } catch (\Exception $innerE) {
                $this->handleAttributeError($innerE, 'fallback add to cart');
            }
        }
    }

    public function removeFromCart($cartKey)
    {
        if (isset($this->cart[$cartKey])) {
            unset($this->cart[$cartKey]);
            $this->calculateTotal();

            Notification::make()
                ->title(__('message.Product removed from cart'))
                ->success()
                ->send();
        }
    }

    public function updateQuantity($cartKey, $action)
    {
        if (!isset($this->cart[$cartKey]))
            return;

        if ($action === 'increase') {
            if ($this->cart[$cartKey]['quantity'] < $this->cart[$cartKey]['max_quantity']) {
                $this->cart[$cartKey]['quantity']++;
            } else {
                Notification::make()
                    ->title(__('message.Maximum quantity reached'))
                    ->warning()
                    ->send();
            }
        } else {
            if ($this->cart[$cartKey]['quantity'] > 1) {
                $this->cart[$cartKey]['quantity']--;
            } else {
                $this->removeFromCart($cartKey);
            }
        }

        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        if (!$this->selectedAddress) {
            Notification::make()
                ->title(__('message.Error'))
                ->body(__('message.Please select an address'))
                ->danger()
                ->send();
            $this->cart = [];
            return;
        }
        $this->subtotal = collect($this->cart)->sum(function ($item) {
            $itemTotal = $item['price'] * $item['quantity'];
            return $itemTotal;
        });

        $this->discount = 0;

        if ($this->coupon) {
            $discountType = $this->coupon->discount_type;
            if (is_object($discountType)) {
                $discountType = $discountType->value;
            }

            if ($discountType === 'percentage') {
                $this->discount = ($this->subtotal * $this->coupon->discount_value) / 100;
            } else {
                $this->discount = $this->coupon->discount_value;
            }

            if ($this->discount > $this->subtotal) {
                $this->discount = $this->subtotal;
            }
        }

        $this->total = max(0, $this->subtotal - $this->discount);

        $addressRepo = app(AddressRepositoryInterface::class);
        $settingRepo = app(SettingRepositoryInterface::class);
        $cityRepo = app(CityRepositoryInterface::class);

        $firebaseService = new DeliveryFeeService($addressRepo, $settingRepo, $cityRepo);
        $deliveryFee = $firebaseService->calculateDeliveryFee($this->selectedAddress->id, $this->data['area_id']);

        if (!$deliveryFee['success']) {
            Notification::make()
                ->title(__('message.Error'))
                ->body($deliveryFee['message'])
                ->danger()
                ->send();
            $this->dispatch('refresh-totals');
            return;
        }
        $this->deliveryFee = $deliveryFee['data'];

        $this->total += $deliveryFee['data'];

        $this->dispatch('refresh-totals');
    }

    public function create()
    {
        try {
            // Validate form data
            $this->form->validate();

            // Additional validation
            if (!$this->selectedUser) {
                Notification::make()
                    ->title(__('message.Error'))
                    ->body(__('message.Please select a customer'))
                    ->danger()
                    ->send();
                return;
            }

            if (!$this->selectedAddress && count($this->addresses) > 0) {
                Notification::make()
                    ->title(__('message.Error'))
                    ->body(__('message.Please select a delivery address'))
                    ->danger()
                    ->send();
                return;
            }

            // Validate cart is not empty
            if (empty($this->cart)) {
                Notification::make()
                    ->title(__('message.Error'))
                    ->body(__('message.Cart is empty'))
                    ->danger()
                    ->send();
                return;
            }

            DB::beginTransaction();



            // Create the order
            $order = Order::create([
                'user_id' => $this->data['user_id'],
                'status' => OrderStatus::CONFIRMED->value,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'subtotal'   => $this->subtotal,
                'total'      => $this->total,
                'discount'   => $this->discount,
                'delivery_fee' => $this->deliveryFee,
                'coupon_id'  => $this->coupon ? $this->coupon->id : null,
                'notes'      => $this->data['notes'] ?? null,
                'order_type' => 'pos',
                'address_id' => $this->data['address_id'],
                'area_id'    => $this->data['area_id'],
                'created_by' => auth()->id(),
            ]);

            // // If there's an address selected
            // if ($this->selectedAddress) {
            //     $address = Address::find($this->data['address_id']);
            //     $order->update([
            //         'address' => $address->address,
            //         'latitude' => $address->lat,
            //         'longitude' => $address->lng,
            //         'city_id' => $address->city_id,
            //         'area_id' => $address->area_id ?? null,
            //     ]);
            // }
            // Create order details
            foreach ($this->cart as $cartKey => $item) {
                $product = Product::find($item['id']);

                $orderDetail = $order->orderDetails()->create([
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                // Save attribute values for this order detail if any
                if (!empty($item['attributes'])) {
                    foreach ($item['attributes'] as $attr) {
                        $orderDetail->attributeValues()->create([
                            'attribute_value_id' => $attr['value_id'],
                        ]);
                    }
                }

                // Update product quantity
                $product->decrement('quantity', $item['quantity']);
            }

            DB::commit();

            Notification::make()
                ->title(__('message.Order created successfully'))
                ->success()
                ->send();

            $repo = app(NotificationRepositoryInterface::class);
            $firebaseService = new FirebaseService($repo);
            if ($this->selectedUser->fcm_token != null) {
                $result = $firebaseService->sendNotification(
                    'New Order',
                    'A new order has been placed.',
                    'user',
                    $this->selectedUser->fcm_token,
                    true,
                    $this->selectedUser->id,
                    null,
                );
                if (!$result['success']) {
                    Notification::make()
                        ->title('Error sending notification')
                        ->body($result['message'])
                        ->danger()
                        ->send();
                }
            }
            // Reset form and cart
            $this->form->fill();
            $this->selectedUser = null;
            $this->selectedAddress = null;
            $this->addresses = [];
            $this->cart = [];
            $this->subtotal = 0;
            $this->total = 0;
            $this->discount = 0;
            $this->coupon = null;
            $this->data = [];
            // return redirect()->route('filament.admin.resources.orders.edit', ['record' => $order->id]);

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title(__('message.Error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label(__('message.Create Order'))
                ->submit('create')
                ->color('primary')
                ->icon('heroicon-o-shopping-cart')
                ->iconPosition(IconPosition::After)
                ->size('lg')
                ->disabled(fn() => empty($this->cart) || !$this->selectedUser),
        ];
    }

    #[On('address-added')]
    public function addressAdded($addressId)
    {
        // Refresh addresses list
        if ($this->selectedUser) {
            // Reload addresses from database
            $this->addresses = Address::where('user_id', $this->selectedUser->id)->get();

            // Set the newly added address as selected
            if ($addressId) {
                $this->data['address_id'] = $addressId;
                $this->selectedAddress = Address::find($addressId);
            }

            // Force re-render to refresh the form with updated address options
            $this->dispatch('refresh-form');

            // Force Filament to revalidate the form
            $this->fillForm();
        }
    }

    /**
     * Refill the form with current data
     */
    protected function fillForm()
    {
        $this->form->fill([
            'user_id' => $this->selectedUser?->id,
            'address_id' => $this->selectedAddress?->id,
            'coupon_id' => $this->coupon?->id,
            // Include any other form fields that need to be updated
        ]);
    }

    /**
     * Handle Product Attribute Errors
     */
    protected function handleAttributeError(\Exception $e, string $context = 'attribute operation')
    {
        $errorMessage = $e->getMessage();
        $trace = $e->getTraceAsString();
        $file = $e->getFile();
        $line = $e->getLine();

        // Log detailed error for debugging
        \Illuminate\Support\Facades\Log::error("Error in {$context}: {$errorMessage}", [
            'exception' => get_class($e),
            'file' => $file,
            'line' => $line,
            'trace' => $trace
        ]);

        // Show general user-friendly notification
        Notification::make()
            ->title(__('message.Error in product attributes'))
            ->body(__('message.There was a problem with product attributes'))
            ->danger()
            ->send();

        return false;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
