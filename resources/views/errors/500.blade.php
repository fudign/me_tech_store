@extends('layouts.app')

@section('title', 'Ошибка сервера')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 px-6 py-16">
    <div class="max-w-2xl w-full text-center">
        <!-- Error Icon -->
        <div class="mb-8 flex justify-center">
            <iconify-icon icon="solar:danger-triangle-linear" width="120" class="text-red-300"></iconify-icon>
        </div>

        <!-- Error Code -->
        <h1 class="text-6xl md:text-8xl font-bold text-gray-900 mb-4">500</h1>

        <!-- Error Message -->
        <h2 class="text-2xl md:text-3xl font-semibold text-gray-700 mb-4">
            Ошибка сервера
        </h2>

        <!-- Explanation -->
        <p class="text-gray-500 text-lg mb-8 leading-relaxed">
            Произошла внутренняя ошибка сервера. Мы уже работаем над исправлением.
        </p>

        <!-- Action Button -->
        <div class="flex justify-center">
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-sm hover:shadow-md">
                <iconify-icon icon="solar:home-2-linear" width="20"></iconify-icon>
                <span>На главную</span>
            </a>
        </div>

        <!-- Support Info -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <p class="text-sm text-gray-500 mb-2">Если проблема повторяется, свяжитесь с нами:</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center text-sm">
                <a href="tel:+996555000000" class="flex items-center gap-2 text-blue-600 hover:text-blue-700 hover:underline">
                    <iconify-icon icon="solar:phone-calling-linear" width="16"></iconify-icon>
                    +996 (555) 00-00-00
                </a>
                <span class="hidden sm:inline text-gray-300">•</span>
                <a href="mailto:sales@gadget.kg" class="flex items-center gap-2 text-blue-600 hover:text-blue-700 hover:underline">
                    <iconify-icon icon="solar:letter-linear" width="16"></iconify-icon>
                    sales@gadget.kg
                </a>
            </div>
        </div>

        <!-- Troubleshooting Tips -->
        <div class="mt-8 bg-blue-50 rounded-lg p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Что можно попробовать:</h3>
            <ul class="text-sm text-gray-600 space-y-2 text-left max-w-md mx-auto">
                <li class="flex items-start gap-2">
                    <iconify-icon icon="solar:check-circle-linear" width="18" class="text-blue-600 mt-0.5 shrink-0"></iconify-icon>
                    <span>Обновите страницу через несколько минут</span>
                </li>
                <li class="flex items-start gap-2">
                    <iconify-icon icon="solar:check-circle-linear" width="18" class="text-blue-600 mt-0.5 shrink-0"></iconify-icon>
                    <span>Очистите кэш и cookies браузера</span>
                </li>
                <li class="flex items-start gap-2">
                    <iconify-icon icon="solar:check-circle-linear" width="18" class="text-blue-600 mt-0.5 shrink-0"></iconify-icon>
                    <span>Попробуйте использовать другой браузер</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
