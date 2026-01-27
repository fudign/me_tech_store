@extends('layouts.app')

@section('title', 'Избранное - Xiaomi Store')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Избранное</h1>
            <p class="text-gray-600">Сохраненные товары ({{ count($products) }})</p>
        </div>

        @if($products->isEmpty())
            <!-- Empty State -->
            <div class="bg-white rounded-2xl p-12 text-center shadow-sm">
                <div class="text-gray-300 mb-4">
                    <iconify-icon icon="solar:heart-broken-linear" width="80" stroke-width="1.5"></iconify-icon>
                </div>
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Ваш список избранного пуст</h2>
                <p class="text-gray-600 mb-6">Добавьте товары в избранное, чтобы не потерять их</p>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-900 text-white rounded-full hover:bg-gray-800 transition-all">
                    <iconify-icon icon="solar:shop-linear" width="20"></iconify-icon>
                    <span>Перейти в каталог</span>
                </a>
            </div>
        @else
            <!-- Products Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" x-data="{ wishlistIds: {{ json_encode($products->pluck('id')->toArray()) }} }">
                @foreach($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
