@extends('layouts.app')

@section('title', 'Страница не найдена')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 px-6 py-16">
    <div class="max-w-2xl w-full text-center">
        <!-- Error Icon -->
        <div class="mb-8 flex justify-center">
            <iconify-icon icon="solar:ghost-linear" width="120" class="text-gray-300"></iconify-icon>
        </div>

        <!-- Error Code -->
        <h1 class="text-6xl md:text-8xl font-bold text-gray-900 mb-4">404</h1>

        <!-- Error Message -->
        <h2 class="text-2xl md:text-3xl font-semibold text-gray-700 mb-4">
            Страница не найдена
        </h2>

        <!-- Explanation -->
        <p class="text-gray-500 text-lg mb-8 leading-relaxed">
            К сожалению, запрашиваемая страница не существует или была удалена.
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-sm hover:shadow-md">
                <iconify-icon icon="solar:home-2-linear" width="20"></iconify-icon>
                <span>На главную</span>
            </a>
            <a href="{{ url()->previous() }}"
               class="inline-flex items-center gap-2 px-8 py-3 border-2 border-gray-300 hover:border-gray-400 text-gray-700 hover:text-gray-900 font-medium rounded-lg transition-colors bg-white hover:bg-gray-50">
                <iconify-icon icon="solar:arrow-left-linear" width="20"></iconify-icon>
                <span>Назад</span>
            </a>
        </div>

        <!-- Helpful Links -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <p class="text-sm text-gray-500 mb-4">Возможно, вы искали:</p>
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="{{ route('products.index') }}" class="text-sm text-blue-600 hover:text-blue-700 hover:underline">
                    Все товары
                </a>
                <span class="text-gray-300">•</span>
                <a href="{{ route('cart.index') }}" class="text-sm text-blue-600 hover:text-blue-700 hover:underline">
                    Корзина
                </a>
                <span class="text-gray-300">•</span>
                <a href="{{ route('home') }}#contact" class="text-sm text-blue-600 hover:text-blue-700 hover:underline">
                    Контакты
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
