@extends('layouts.app')

@section('title', $product->meta_title ?? $product->name . ' - Mi Tech Store')
@section('meta_description', $product->meta_description ?? $product->description)

@section('content')
<div class="container mx-auto px-4 md:px-6 py-6 md:py-8 max-w-7xl">
    <!-- Breadcrumb -->
    <nav class="text-xs md:text-sm text-gray-600 mb-4 overflow-x-auto whitespace-nowrap pb-2">
        <a href="{{ route('home') }}" class="hover:underline">Главная</a>
        @foreach($product->categories as $category)
            / <a href="{{ route('category.show', $category) }}" class="hover:underline">{{ $category->name }}</a>
        @endforeach
        / <span class="text-gray-900">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
        <!-- Product Images -->
        <div>
            @if($product->main_image)
                <x-product-image
                    :image="$product->main_image"
                    :alt="$product->name"
                    size="medium"
                    class="w-full rounded-lg mb-4 bg-gray-50 p-4" />
            @else
                <div class="w-full aspect-square bg-gray-100 rounded-lg mb-4 flex items-center justify-center">
                    <div class="text-gray-300">
                        <iconify-icon icon="solar:gallery-linear" width="128"></iconify-icon>
                    </div>
                </div>
            @endif

            @if($product->images && is_array($product->images))
                <div class="grid grid-cols-4 gap-2">
                    @foreach($product->images as $image)
                        <x-product-image
                            :image="is_array($image) ? $image['path'] : $image"
                            :alt="$product->name"
                            size="large"
                            class="w-full h-20 object-cover rounded cursor-pointer hover:opacity-75 bg-gray-50" />
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Product Info & Contact -->
        <div>
            <h1 class="text-2xl md:text-3xl font-bold mb-4 leading-tight">{{ $product->name }}</h1>

            <div class="flex flex-wrap items-baseline gap-2 md:gap-3 mb-4 md:mb-6">
                <span class="text-3xl md:text-4xl font-bold text-orange-600">
                    {{ number_format($product->price / 100, 0) }} сом
                </span>

                @if($product->old_price)
                    <span class="text-lg md:text-xl line-through text-gray-500">
                        {{ number_format($product->old_price / 100, 0) }} сом
                    </span>
                    <span class="bg-red-500 text-white px-2 py-1 rounded text-xs md:text-sm font-semibold">
                        -{{ round((($product->old_price - $product->price) / $product->old_price) * 100) }}%
                    </span>
                @endif
            </div>

            <div class="mb-4 md:mb-6">
                @php
                    $statusText = match($product->availability_status ?? 'in_stock') {
                        'in_stock' => '✓ В наличии',
                        'out_of_stock' => 'Нет в наличии',
                        'coming_soon' => 'Скоро будет',
                        default => '✓ В наличии'
                    };
                    $statusClass = match($product->availability_status ?? 'in_stock') {
                        'in_stock' => 'text-green-600',
                        'out_of_stock' => 'text-red-600',
                        'coming_soon' => 'text-blue-600',
                        default => 'text-green-600'
                    };
                @endphp
                <p class="{{ $statusClass }} font-semibold text-sm md:text-base">{{ $statusText }}</p>
            </div>

            <!-- Add to Cart Button -->
            <div x-data="{ adding: false }" class="mb-6 md:mb-8">
                @if(($product->availability_status ?? 'in_stock') === 'in_stock')
                    <button @click="addToCart({{ $product->id }})"
                            :disabled="adding"
                            class="w-full bg-brand-500 text-white py-3.5 md:py-4 rounded-xl font-semibold hover:bg-brand-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed active:scale-98 text-sm md:text-base">
                        <span x-show="!adding">Добавить в корзину</span>
                        <span x-show="adding">Добавление...</span>
                    </button>
                @else
                    @php
                        $buttonText = match($product->availability_status ?? 'out_of_stock') {
                            'out_of_stock' => 'Нет в наличии',
                            'coming_soon' => 'Скоро будет',
                            default => 'Нет в наличии'
                        };
                    @endphp
                    <button disabled class="w-full bg-gray-300 text-gray-500 py-3.5 md:py-4 rounded-xl font-semibold cursor-not-allowed text-sm md:text-base">
                        {{ $buttonText }}
                    </button>
                @endif
            </div>

            <!-- Contact Information Block -->
            @php
                $contactInfo = \App\Models\Setting::get('product.contact_info', '');
            @endphp
            @if($contactInfo)
                <div class="mt-4 md:mt-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <div class="font-semibold text-gray-900 mb-2 text-sm md:text-base flex items-center gap-2">
                        <iconify-icon icon="solar:phone-calling-linear" width="20"></iconify-icon>
                        Уточняйте наличие:
                    </div>
                    <div class="space-y-1 text-xs md:text-sm text-gray-700">
                        @foreach(explode("\n", $contactInfo) as $line)
                            @if(trim($line))
                                <div>{{ trim($line) }}</div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <script>
            function addToCart(productId) {
                this.adding = true;

                fetch('/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ product_id: productId, quantity: 1 })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Dispatch event to show toast
                        window.dispatchEvent(new CustomEvent('cart-added', {
                            detail: { message: data.message }
                        }));

                        // Update header cart count
                        window.dispatchEvent(new CustomEvent('cart-updated', {
                            detail: { count: data.cart_count }
                        }));
                    }
                })
                .catch(error => {
                    console.error('Error adding to cart:', error);
                    window.dispatchEvent(new CustomEvent('cart-added', {
                        detail: { message: 'Ошибка при добавлении в корзину' }
                    }));
                })
                .finally(() => {
                    this.adding = false;
                });
            }
            </script>
        </div>
    </div>

    <!-- Description and Specifications (below images) -->
    @if($product->description || $product->specifications)
        <div class="clear-both"></div>
        <div class="mt-8 md:mt-12 space-y-6 md:space-y-8 w-full">
            @if($product->description)
                <div class="bg-white rounded-xl border border-gray-200 p-4 md:p-6">
                    <h2 class="text-lg md:text-xl font-semibold mb-3 md:mb-4 flex items-center gap-2">
                        <iconify-icon icon="solar:document-text-linear" width="24"></iconify-icon>
                        Описание
                    </h2>
                    <p class="text-sm md:text-base text-gray-700 leading-relaxed">{{ $product->description }}</p>
                </div>
            @endif

            @if($product->specifications)
                <div class="bg-white rounded-xl border border-gray-200 p-4 md:p-6">
                    <h2 class="text-lg md:text-xl font-semibold mb-3 md:mb-4 flex items-center gap-2">
                        <iconify-icon icon="solar:list-check-linear" width="24"></iconify-icon>
                        Характеристики
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm md:text-base">
                            @foreach($product->specifications as $key => $value)
                                <tr class="border-b border-gray-100 last:border-0">
                                    <td class="py-2.5 md:py-3 text-gray-600 pr-4">{{ $key }}</td>
                                    <td class="py-2.5 md:py-3 font-semibold text-gray-900">{{ $value }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
