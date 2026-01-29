@extends('layouts.app')

@section('content')

<!-- Hero Section -->
@php
    $heroBadge = \App\Models\Setting::get('hero.badge', 'Новинка');
    $heroTitle = \App\Models\Setting::get('hero.title', 'Xiaomi 14 Ultra');
    $heroSubtitle = \App\Models\Setting::get('hero.subtitle', 'Оптика Leica.');
    $heroDescription = \App\Models\Setting::get('hero.description', 'Легендарная оптика, процессор Snapdragon 8 Gen 3 и новый иммерсивный дисплей.');
    $heroImageUrl = \App\Models\Setting::get('hero.image_url', 'https://hoirqrkdgbmvpwutwuwj.supabase.co/storage/v1/object/public/assets/assets/917d6f93-fb36-439a-8c48-884b67b35381_1600w.jpg');
    $heroProductId = \App\Models\Setting::get('hero.product_id');

    // Determine button links
    if ($heroProductId) {
        $heroProduct = \App\Models\Product::find($heroProductId);
        $buyLink = $heroProduct ? route('product.show', $heroProduct->slug) : route('products.index');
        $detailsLink = $buyLink;
    } else {
        $buyLink = route('products.index');
        $detailsLink = route('products.index');
    }
@endphp

<section class="max-w-7xl mx-auto px-4 md:px-6 py-6 md:py-12">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-8 items-center bg-gray-900 text-white rounded-2xl md:rounded-3xl p-6 md:p-12 overflow-hidden relative shadow-xl">
        <!-- Decorative Elements -->
        <div class="absolute top-0 right-0 w-[300px] md:w-[500px] h-[300px] md:h-[500px] bg-brand-500/20 blur-[80px] md:blur-[100px] rounded-full pointer-events-none -translate-y-1/2 translate-x-1/2"></div>

        <div class="md:col-span-6 relative z-10 space-y-4 md:space-y-6">
            @if($heroBadge)
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-white/20 bg-white/10 backdrop-blur text-xs font-medium text-white">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-pulse"></span>
                {{ $heroBadge }}
            </div>
            @endif
            <h1 class="text-3xl md:text-5xl lg:text-6xl font-semibold tracking-tight leading-tight">
                {{ $heroTitle }} @if($heroSubtitle)<br><span class="text-gray-400 text-2xl md:text-4xl lg:text-5xl">{{ $heroSubtitle }}</span>@endif
            </h1>
            @if($heroDescription)
            <p class="text-gray-300 text-sm md:text-base lg:text-lg max-w-md font-light leading-relaxed">
                {{ $heroDescription }}
            </p>
            @endif
            <div class="flex flex-wrap items-center gap-3 md:gap-4 pt-2">
                <a href="{{ $buyLink }}" class="bg-white text-gray-900 px-5 md:px-6 py-2.5 md:py-3 rounded-full text-sm font-medium hover:bg-brand-50 transition-colors flex items-center gap-2 active:scale-95">
                    Купить
                    <iconify-icon icon="solar:arrow-right-linear" stroke-width="2" width="18"></iconify-icon>
                </a>
                <a href="{{ $detailsLink }}" class="px-5 md:px-6 py-2.5 md:py-3 rounded-full text-sm font-medium border border-white/20 hover:bg-white/10 transition-colors text-white active:scale-95">
                    Подробнее
                </a>
            </div>
        </div>

        <div class="md:col-span-6 flex justify-center relative z-10 mt-4 md:mt-0">
            <!-- Hero Image -->
            <div class="relative w-full max-w-xs md:max-w-md aspect-square flex items-center justify-center">
                 <img src="{{ $heroImageUrl }}" alt="{{ $heroTitle }}" class="object-contain drop-shadow-2xl hover:scale-105 transition-transform duration-500 select-none">
            </div>
        </div>
    </div>
</section>

<!-- Features Grid -->
<section class="max-w-7xl mx-auto px-4 md:px-6 pb-8 md:pb-12">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
        <div class="p-4 md:p-6 bg-white border border-gray-200 rounded-xl md:rounded-2xl flex items-start gap-3 md:gap-4 hover:border-brand-500/50 hover:shadow-lg hover:shadow-brand-500/5 transition-all group">
            <div class="w-10 h-10 rounded-full bg-brand-50 flex items-center justify-center text-brand-500 shrink-0 group-hover:scale-110 transition-transform">
                <iconify-icon icon="solar:verified-check-linear" width="22" stroke-width="1.5"></iconify-icon>
            </div>
            <div>
                <h3 class="font-medium text-gray-900 mb-1 text-sm md:text-base">Официальная гарантия</h3>
                <p class="text-xs md:text-sm text-gray-500 leading-relaxed">1 год официальной гарантии в авторизованных сервисных центрах.</p>
            </div>
        </div>
        <div class="p-4 md:p-6 bg-white border border-gray-200 rounded-xl md:rounded-2xl flex items-start gap-3 md:gap-4 hover:border-brand-500/50 hover:shadow-lg hover:shadow-brand-500/5 transition-all group">
            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 shrink-0 group-hover:scale-110 transition-transform">
                <iconify-icon icon="solar:box-minimalistic-linear" width="22" stroke-width="1.5"></iconify-icon>
            </div>
            <div>
                <h3 class="font-medium text-gray-900 mb-1 text-sm md:text-base">Быстрая доставка</h3>
                <p class="text-xs md:text-sm text-gray-500 leading-relaxed">Бесплатная доставка по городу при заказе от 5000 сом. Отправка в день заказа.</p>
            </div>
        </div>
        <div class="p-4 md:p-6 bg-white border border-gray-200 rounded-xl md:rounded-2xl flex items-start gap-3 md:gap-4 hover:border-brand-500/50 hover:shadow-lg hover:shadow-brand-500/5 transition-all group">
            <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-500 shrink-0 group-hover:scale-110 transition-transform">
                <iconify-icon icon="solar:card-linear" width="22" stroke-width="1.5"></iconify-icon>
            </div>
            <div>
                <h3 class="font-medium text-gray-900 mb-1 text-sm md:text-base">Рассрочка и кредит</h3>
                <p class="text-xs md:text-sm text-gray-500 leading-relaxed">Выгодные условия рассрочки через банки-партнеры. 0% переплаты.</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Grid -->
<section class="max-w-7xl mx-auto px-4 md:px-6 pb-12 md:pb-16">
    <div class="flex items-center justify-between mb-6 md:mb-8">
        <h2 class="text-lg md:text-2xl font-semibold tracking-tight text-gray-900">Категории</h2>
        <a href="{{ route('products.index') }}" class="text-xs md:text-sm font-medium text-brand-600 hover:text-brand-500 flex items-center gap-1 transition-colors">
            <span class="hidden sm:inline">Все товары</span>
            <span class="sm:hidden">Все</span>
            <iconify-icon icon="solar:arrow-right-linear" width="16"></iconify-icon>
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4">
        @foreach($categories as $category)
            <a href="{{ route('category.show', $category) }}" class="flex flex-col items-center gap-2 md:gap-3 p-4 md:p-6 bg-white border border-gray-200 rounded-xl md:rounded-2xl hover:border-gray-300 hover:shadow-md transition-all group active:scale-95">
                <div class="text-gray-400 group-hover:text-brand-500 transition-colors">
                    <iconify-icon icon="solar:smartphone-2-linear" width="28" stroke-width="1.5" class="md:w-8"></iconify-icon>
                </div>
                <div class="text-center">
                    <span class="text-xs md:text-sm font-medium text-gray-900 line-clamp-1">{{ $category->name }}</span>
                    <p class="text-[10px] md:text-xs text-gray-600">{{ $category->products_count }} товаров</p>
                </div>
            </a>
        @endforeach
    </div>
</section>

<!-- Popular Products -->
<section class="max-w-7xl mx-auto px-4 md:px-6 pb-12 md:pb-20">
    <div class="flex items-center justify-between mb-6 md:mb-8">
        <h2 class="text-lg md:text-2xl font-semibold tracking-tight text-gray-900">Популярные товары</h2>
    </div>

    @if($popularProducts->count() > 0)
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">
            @foreach($popularProducts as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-gray-400 mb-4">
                <iconify-icon icon="solar:box-linear" width="48" class="md:w-16"></iconify-icon>
            </div>
            <p class="text-sm md:text-base text-gray-500">Товары появятся здесь после добавления через админ-панель</p>
        </div>
    @endif
</section>

@endsection
