@props(['product'])

<div class="group bg-white border border-gray-200 rounded-2xl p-4 hover:shadow-xl hover:shadow-gray-200/50 hover:border-gray-300 transition-all duration-300 relative flex flex-col"
     x-data="productCard()">
    @if($product->old_price)
        <div class="absolute top-4 left-4 z-10">
            <span class="bg-brand-500 text-white text-[10px] font-semibold px-2 py-1 rounded-md uppercase tracking-wide">Акция</span>
        </div>
    @endif
    <button @click.prevent="toggleWishlist({{ $product->id }})"
            class="absolute top-4 right-4 z-10 transition-colors"
            :class="wishlistIds.includes({{ $product->id }}) ? 'text-red-500' : 'text-gray-300 hover:text-red-500'">
        <iconify-icon icon="solar:heart-linear" width="20" stroke-width="2"></iconify-icon>
    </button>

    <a href="{{ route('product.show', $product) }}" class="flex flex-col flex-1">
        <div class="aspect-[4/3] flex items-center justify-center mb-4 p-2 bg-gray-50 rounded-xl overflow-hidden">
            @if($product->main_image)
                <x-product-image
                    :image="$product->main_image"
                    :alt="$product->name"
                    size="thumb"
                    class="h-full object-contain mix-blend-multiply group-hover:scale-110 transition-transform duration-500" />
            @else
                <div class="text-gray-300">
                    <iconify-icon icon="solar:gallery-linear" width="64"></iconify-icon>
                </div>
            @endif
        </div>

        <div class="space-y-2 flex-1">
            <h3 class="font-medium text-gray-900 leading-snug group-hover:text-brand-600 transition-colors">{{ $product->name }}</h3>

            @php
                $statusText = match($product->availability_status ?? 'in_stock') {
                    'in_stock' => 'В наличии',
                    'out_of_stock' => 'Нет в наличии',
                    'coming_soon' => 'Скоро будет',
                    default => 'В наличии'
                };
                $statusClass = match($product->availability_status ?? 'in_stock') {
                    'in_stock' => 'text-green-600',
                    'out_of_stock' => 'text-red-600',
                    'coming_soon' => 'text-blue-600',
                    default => 'text-green-600'
                };
            @endphp
            <p class="text-xs {{ $statusClass }}">{{ $statusText }}</p>
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
            <div>
                @if($product->old_price)
                    <span class="text-xs text-gray-400 line-through block">{{ number_format($product->old_price / 100, 0) }} сом</span>
                @endif
                <span class="text-lg font-semibold text-gray-900">{{ number_format($product->price / 100, 0) }} сом</span>
            </div>
            <button class="w-9 h-9 bg-gray-900 rounded-full flex items-center justify-center text-white hover:bg-brand-500 transition-colors shadow-sm active:scale-95">
                <iconify-icon icon="solar:cart-plus-linear" width="18" stroke-width="2"></iconify-icon>
            </button>
        </div>
    </a>
</div>

@once
@push('scripts')
<script>
function productCard() {
    return {
        wishlistIds: @json(session('wishlist', [])),
        async toggleWishlist(productId) {
            try {
                const response = await fetch('/wishlist/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ product_id: productId })
                });
                const data = await response.json();

                if (data.success) {
                    // Update local state
                    if (data.inWishlist) {
                        this.wishlistIds.push(productId);
                    } else {
                        this.wishlistIds = this.wishlistIds.filter(id => id !== productId);
                    }

                    // Dispatch event for header counter
                    window.dispatchEvent(new CustomEvent('wishlist-updated', {
                        detail: { count: data.count }
                    }));

                    // Show toast notification
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: data.message, type: 'success' }
                    }));
                }
            } catch (error) {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: 'Ошибка при обновлении избранного', type: 'error' }
                }));
            }
        }
    }
}
</script>
@endpush
@endonce
