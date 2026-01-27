@extends('layouts.admin')

@section('title', 'Редактировать купон')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.coupons.index') }}" class="text-blue-600 hover:text-blue-800">
            ← Назад к списку купонов
        </a>
    </div>

    <h1 class="text-3xl font-bold mb-6">Редактировать купон</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Code -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Код купона <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="code"
                           value="{{ old('code', $coupon->code) }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono">
                    @error('code')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Тип скидки <span class="text-red-500">*</span>
                    </label>
                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>Фиксированная сумма</option>
                        <option value="percentage" {{ old('type', $coupon->type) === 'percentage' ? 'selected' : '' }}>Процент от суммы</option>
                    </select>
                    @error('type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Value -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Значение <span class="text-red-500">*</span>
                    </label>
                    <input type="number"
                           name="value"
                           value="{{ old('value', $coupon->type === 'fixed' ? $coupon->value / 100 : $coupon->value) }}"
                           required
                           step="0.01"
                           min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('value')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Min order amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Минимальная сумма заказа (сом)
                    </label>
                    <input type="number"
                           name="min_order_amount"
                           value="{{ old('min_order_amount', $coupon->min_order_amount / 100) }}"
                           step="0.01"
                           min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('min_order_amount')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Max discount amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Максимальная скидка (сом)
                    </label>
                    <input type="number"
                           name="max_discount_amount"
                           value="{{ old('max_discount_amount', $coupon->max_discount_amount ? $coupon->max_discount_amount / 100 : null) }}"
                           step="0.01"
                           min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('max_discount_amount')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Usage limit -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Лимит использований
                    </label>
                    <input type="number"
                           name="usage_limit"
                           value="{{ old('usage_limit', $coupon->usage_limit) }}"
                           min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Использовано: {{ $coupon->used_count }} раз</p>
                    @error('usage_limit')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Start date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Дата начала
                    </label>
                    <input type="datetime-local"
                           name="starts_at"
                           value="{{ old('starts_at', $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('starts_at')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Expiry date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Дата окончания
                    </label>
                    <input type="datetime-local"
                           name="expires_at"
                           value="{{ old('expires_at', $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('expires_at')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Описание
                </label>
                <textarea name="description"
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description', $coupon->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Is Active -->
            <div class="mt-6">
                <label class="flex items-center">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Активен</span>
                </label>
            </div>

            <!-- Submit -->
            <div class="mt-6 flex items-center space-x-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Сохранить изменения
                </button>
                <a href="{{ route('admin.coupons.index') }}" class="text-gray-600 hover:text-gray-900">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
