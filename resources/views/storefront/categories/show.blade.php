@extends('layouts.app')

@section('title', $category->meta_title ?? $category->name . ' - Xiaomi Store')
@section('meta_description', $category->meta_description ?? $category->description)

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-600 mb-4">
        <a href="{{ route('home') }}" class="hover:underline">Главная</a>
        / <span>{{ $category->name }}</span>
    </nav>

    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900 mb-2">{{ $category->name }}</h1>
        @if($category->description)
            <p class="text-gray-600">{{ $category->description }}</p>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Filter Sidebar -->
        <div class="lg:col-span-1">
            <x-product-filter :memoryOptions="$memoryOptions ?? []"
                              :colorOptions="$colorOptions ?? []" />
        </div>

        <!-- Product Grid -->
        <div class="lg:col-span-3">
            <div class="flex items-center justify-between mb-6">
                <div class="text-sm text-gray-500">
                    Найдено: {{ $products->total() }} товаров
                </div>
            </div>

            @if($products->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    @foreach($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-gray-400 mb-4">
                        <iconify-icon icon="solar:box-linear" width="64"></iconify-icon>
                    </div>
                    <p class="text-gray-500">В этой категории нет товаров, соответствующих фильтрам</p>
                    <a href="{{ route('category.show', $category) }}" class="text-brand-600 hover:text-brand-500 mt-4 inline-block">Сбросить фильтры</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
