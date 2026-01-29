@extends('layouts.app')

@section('title', 'Корзина - Mi Tech')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <h1 class="text-3xl font-bold mb-8">Корзина</h1>

    @if($items->isEmpty())
        <!-- Empty State -->
        <div class="text-center py-16">
            <div class="mb-6 text-gray-300">
                <iconify-icon icon="solar:cart-large-minimalistic-linear" width="96" stroke-width="1.5"></iconify-icon>
            </div>
            <h2 class="text-2xl font-semibold mb-2 text-gray-900">Ваша корзина пуста</h2>
            <p class="text-gray-500 mb-6">Добавьте товары в корзину, чтобы оформить заказ</p>
            <a href="{{ route('products.index') }}" class="inline-block bg-brand-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-600 transition-colors">
                Перейти к покупкам
            </a>
        </div>
    @else
        <!-- Cart Items -->
        <div x-data="cartManager()" class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-4">
                @foreach($items as $item)
                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100" x-data="{ itemId: '{{ $item->id }}' }">
                    <div class="flex gap-4">
                        <!-- Product Image -->
                        <div class="w-24 h-24 bg-gray-50 rounded flex-shrink-0">
                            @if($item->attributes->image)
                                <img src="{{ asset('storage/' . $item->attributes->image) }}"
                                     alt="{{ $item->name }}"
                                     class="w-full h-full object-contain p-2">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <iconify-icon icon="solar:gallery-linear" width="40"></iconify-icon>
                                </div>
                            @endif
                        </div>

                        <!-- Product Info -->
                        <div class="flex-1">
                            <a href="{{ route('product.show', $item->attributes->slug) }}"
                               class="font-semibold text-gray-900 hover:text-brand-500 transition-colors">
                                {{ $item->name }}
                            </a>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ number_format($item->price, 0, ',', ' ') }} сом × <span x-text="quantities[itemId]">{{ $item->quantity }}</span>
                            </p>

                            <!-- Quantity Controls -->
                            <div class="flex items-center gap-4 mt-3">
                                <div class="flex items-center border border-gray-200 rounded">
                                    <button @click="decrementQuantity(itemId)"
                                            class="px-3 py-1 hover:bg-gray-50 transition-colors">
                                        -
                                    </button>
                                    <input type="number"
                                           x-model="quantities[itemId]"
                                           @change="updateQuantity(itemId, $event.target.value)"
                                           min="1"
                                           class="w-16 text-center border-x border-gray-200 py-1 focus:outline-none">
                                    <button @click="incrementQuantity(itemId)"
                                            class="px-3 py-1 hover:bg-gray-50 transition-colors">
                                        +
                                    </button>
                                </div>

                                <button @click="removeItem(itemId)"
                                        class="text-sm text-red-500 hover:text-red-700 transition-colors">
                                    Удалить
                                </button>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900">
                                <span x-text="formatPrice(quantities[itemId] * {{ $item->price }})">
                                    {{ number_format($item->price * $item->quantity, 0, ',', ' ') }}
                                </span> сом
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-100 sticky top-24">
                    <h2 class="text-xl font-semibold mb-4">Итого</h2>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-gray-600">
                            <span>Товары (<span x-text="cartCount">{{ $items->count() }}</span>)</span>
                            <span x-text="formatPrice(total) + ' сом'">{{ number_format($total, 0, ',', ' ') }} сом</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Всего:</span>
                                <span x-text="formatPrice(total) + ' сом'">{{ number_format($total, 0, ',', ' ') }} сом</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('checkout.index') }}"
                       class="block w-full bg-brand-500 text-white text-center py-3 rounded-lg font-semibold hover:bg-brand-600 transition-colors">
                        Оформить заказ
                    </a>

                    <a href="{{ route('products.index') }}"
                       class="block w-full text-center text-gray-500 py-2 mt-3 hover:text-gray-900 transition-colors">
                        Продолжить покупки
                    </a>
                </div>
            </div>
        </div>

        <script>
        function cartManager() {
            return {
                quantities: @json($items->pluck('quantity', 'id')),
                total: {{ $total }},
                cartCount: {{ $items->count() }},

                incrementQuantity(itemId) {
                    this.quantities[itemId] = parseInt(this.quantities[itemId]) + 1;
                    this.updateQuantity(itemId, this.quantities[itemId]);
                },

                decrementQuantity(itemId) {
                    if (parseInt(this.quantities[itemId]) > 1) {
                        this.quantities[itemId] = parseInt(this.quantities[itemId]) - 1;
                        this.updateQuantity(itemId, this.quantities[itemId]);
                    }
                },

                updateQuantity(itemId, newQty) {
                    const quantity = parseInt(newQty);
                    if (quantity < 1) {
                        this.quantities[itemId] = 1;
                        return;
                    }

                    fetch(`/cart/${itemId}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ quantity: quantity })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.total = data.total;
                            this.cartCount = data.cart_count;
                            // Update header cart count
                            this.$dispatch('cart-updated', { count: data.cart_count });
                        }
                    })
                    .catch(error => {
                        console.error('Error updating cart:', error);
                    });
                },

                removeItem(itemId) {
                    if (!confirm('Удалить товар из корзины?')) return;

                    fetch(`/cart/${itemId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload page to show updated cart
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error removing item:', error);
                    });
                },

                formatPrice(value) {
                    return new Intl.NumberFormat('ru-RU').format(Math.round(value));
                }
            }
        }
        </script>
    @endif
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
