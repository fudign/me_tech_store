@extends('layouts.app')

@section('title', 'Оформление заказа | Mi Tech')

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 py-6 md:py-12">
    <h1 class="text-2xl md:text-3xl font-semibold text-gray-900 mb-6 md:mb-8">Оформление заказа</h1>

    <!-- Display general errors -->
    @if($errors->has('cart') || $errors->has('checkout'))
        <div class="mb-4 md:mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm md:text-base">
            {{ $errors->first('cart') ?: $errors->first('checkout') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 md:gap-8">
        <!-- Order Summary (Left Column on Desktop) -->
        <div class="lg:col-span-2 order-2 lg:order-1">
            <div class="bg-white rounded-xl border border-gray-200 p-4 md:p-6 lg:sticky lg:top-6">
                <h2 class="text-base md:text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <iconify-icon icon="solar:bag-4-linear" width="22"></iconify-icon>
                    Ваш заказ
                </h2>

                <div class="space-y-3 md:space-y-4 mb-4 md:mb-6">
                    @foreach($items as $item)
                    <div class="flex gap-3 md:gap-4">
                        <div class="w-14 h-14 md:w-16 md:h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                            @if($item->attributes->image)
                                <img src="{{ asset('storage/' . $item->attributes->image) }}"
                                     alt="{{ $item->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <iconify-icon icon="solar:gallery-minimalistic-linear" width="20"></iconify-icon>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-xs md:text-sm font-medium text-gray-900 line-clamp-2">{{ $item->name }}</h3>
                            <p class="text-[10px] md:text-xs text-gray-500 mt-0.5">Количество: {{ $item->quantity }}</p>
                            <p class="text-sm md:text-base font-semibold text-gray-900 mt-1">
                                {{ number_format($item->price * $item->quantity, 0, ',', ' ') }} сом
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <div class="flex justify-between items-center text-base md:text-lg font-semibold text-gray-900">
                        <span>Итого:</span>
                        <span>{{ number_format($total, 0, ',', ' ') }} сом</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Form (Right Column on Desktop) -->
        <div class="lg:col-span-3 order-1 lg:order-2">
            <div class="bg-white rounded-xl border border-gray-200 p-4 md:p-6">
                <h2 class="text-base md:text-lg font-semibold text-gray-900 mb-4 md:mb-6 flex items-center gap-2">
                    <iconify-icon icon="solar:user-linear" width="22"></iconify-icon>
                    Информация для доставки
                </h2>

                <form action="{{ route('checkout.process') }}" method="POST" class="space-y-4 md:space-y-6">
                    @csrf

                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Ваше имя <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all @error('name') border-red-500 @enderror"
                            placeholder="Иван Иванов"
                        >
                        @error('name')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone Field -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Номер телефона <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="tel"
                            name="phone"
                            id="phone"
                            value="{{ old('phone') }}"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all @error('phone') border-red-500 @enderror"
                            placeholder="+996 XXX XXX XXX"
                        >
                        <p class="mt-1.5 text-xs text-gray-500">Формат: +996 XXX XXX XXX</p>
                        @error('phone')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address Field -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            Адрес доставки <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            name="address"
                            id="address"
                            rows="3"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all resize-none @error('address') border-red-500 @enderror"
                            placeholder="Улица, дом, квартира, город"
                        >{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Method Field -->
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                            Способ оплаты <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="payment_method"
                            id="payment_method"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all @error('payment_method') border-red-500 @enderror"
                        >
                            <option value="">Выберите способ оплаты</option>
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>
                                Наличными при получении
                            </option>
                            <option value="online" {{ old('payment_method') === 'online' ? 'selected' : '' }}>
                                Онлайн оплата картой
                            </option>
                            <option value="installment" {{ old('payment_method') === 'installment' ? 'selected' : '' }}>
                                Рассрочка
                            </option>
                        </select>
                        @error('payment_method')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 md:gap-4 pt-4">
                        <button
                            type="submit"
                            class="flex-1 bg-brand-500 hover:bg-brand-600 text-white font-semibold px-6 py-3.5 rounded-xl transition-colors flex items-center justify-center gap-2 active:scale-98 text-sm md:text-base"
                        >
                            <iconify-icon icon="solar:check-circle-linear" width="20"></iconify-icon>
                            Оформить заказ
                        </button>
                        <a
                            href="{{ route('cart.index') }}"
                            class="text-center sm:text-left text-gray-600 hover:text-gray-900 font-medium transition-colors flex items-center justify-center gap-1.5 py-3 text-sm md:text-base"
                        >
                            <iconify-icon icon="solar:arrow-left-linear" width="18"></iconify-icon>
                            К корзине
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
