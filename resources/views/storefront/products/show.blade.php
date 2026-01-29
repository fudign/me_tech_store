@extends('layouts.app')

@section('title', $product->meta_title ?? $product->name . ' - Mi Tech Store')
@section('meta_description', $product->meta_description ?? $product->description)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-600 mb-4">
        <a href="{{ route('home') }}" class="hover:underline">Главная</a>
        @foreach($product->categories as $category)
            / <a href="{{ route('category.show', $category) }}" class="hover:underline">{{ $category->name }}</a>
        @endforeach
        / <span>{{ $product->name }}</span>
    </nav>

    <div class="grid md:grid-cols-2 gap-8">
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

        <!-- Product Info -->
        <div>
            <h1 class="text-3xl font-bold mb-4">{{ $product->name }}</h1>

            <div class="flex items-baseline gap-3 mb-6">
                <span class="text-4xl font-bold text-orange-600">
                    {{ number_format($product->price / 100, 0) }} сом
                </span>

                @if($product->old_price)
                    <span class="text-xl line-through text-gray-500">
                        {{ number_format($product->old_price / 100, 0) }} сом
                    </span>
                    <span class="bg-red-500 text-white px-2 py-1 rounded text-sm">
                        -{{ round((($product->old_price - $product->price) / $product->old_price) * 100) }}%
                    </span>
                @endif
            </div>

            <div class="mb-6">
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
                <p class="{{ $statusClass }} font-semibold">{{ $statusText }}</p>
            </div>

            @if($product->description)
                <div class="prose max-w-none mb-6">
                    <h2 class="text-xl font-semibold mb-2">Описание</h2>
                    <p class="text-gray-700">{{ $product->description }}</p>
                </div>
            @endif

            @if($product->specifications)
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-3">Характеристики</h2>
                    <table class="w-full">
                        @foreach($product->specifications as $key => $value)
                            <tr class="border-b">
                                <td class="py-2 text-gray-600">{{ $key }}</td>
                                <td class="py-2 font-semibold">{{ $value }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endif

            <!-- Add to Cart Button -->
            <div x-data="{ adding: false }">
                @if(($product->availability_status ?? 'in_stock') === 'in_stock')
                    <button @click="addToCart({{ $product->id }})"
                            :disabled="adding"
                            class="w-full bg-brand-500 text-white py-3 rounded-lg font-semibold hover:bg-brand-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
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
                    <button disabled class="w-full bg-gray-300 text-gray-500 py-3 rounded-lg font-semibold cursor-not-allowed">
                        {{ $buttonText }}
                    </button>
                @endif
            </div>

            <!-- Contact Information Block -->
            @php
                $contactInfo = \App\Models\Setting::get('product.contact_info', '');
            @endphp
            @if($contactInfo)
                <div class="mt-8 bg-gradient-to-br from-orange-50 to-orange-100 border-2 border-orange-200 rounded-xl p-6 shadow-sm">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="bg-orange-500 text-white p-2 rounded-lg flex-shrink-0">
                            <iconify-icon icon="solar:phone-calling-bold" width="24"></iconify-icon>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Контакты и адреса</h3>
                            <div class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $contactInfo }}</div>
                        </div>
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
</div>
@endsection
