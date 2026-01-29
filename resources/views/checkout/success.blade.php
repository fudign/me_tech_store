@extends('layouts.app')

@section('title', 'Заказ оформлен | Mi Tech')

@section('content')
<div class="max-w-3xl mx-auto px-6 py-12">
    <!-- Success Icon and Message -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
            <iconify-icon icon="solar:check-circle-bold" width="48" class="text-green-600"></iconify-icon>
        </div>
        <h1 class="text-3xl font-semibold text-gray-900 mb-2">Спасибо за ваш заказ!</h1>
        <p class="text-gray-600">Ваш заказ успешно оформлен и принят в обработку</p>
    </div>

    <!-- Order Details Card -->
    <div class="bg-white rounded-xl border border-gray-200 p-8 mb-6">
        <!-- Order Number -->
        <div class="border-b border-gray-200 pb-6 mb-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-600">Номер заказа:</span>
                <span class="text-lg font-semibold text-gray-900">{{ $order->order_number }}</span>
            </div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-600">Сумма заказа:</span>
                <span class="text-lg font-semibold text-gray-900">{{ $order->formatted_total }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Статус:</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    {{ App\Models\Order::statusLabels()[$order->status] }}
                </span>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <iconify-icon icon="solar:user-linear" width="20"></iconify-icon>
                Информация о получателе
            </h2>
            <div class="space-y-2 text-sm">
                <div class="flex">
                    <span class="text-gray-600 w-32">Имя:</span>
                    <span class="text-gray-900 font-medium">{{ $order->customer_name }}</span>
                </div>
                <div class="flex">
                    <span class="text-gray-600 w-32">Телефон:</span>
                    <span class="text-gray-900 font-medium">{{ $order->customer_phone }}</span>
                </div>
                <div class="flex">
                    <span class="text-gray-600 w-32">Адрес:</span>
                    <span class="text-gray-900 font-medium">{{ $order->customer_address }}</span>
                </div>
                <div class="flex">
                    <span class="text-gray-600 w-32">Оплата:</span>
                    <span class="text-gray-900 font-medium">
                        @switch($order->payment_method)
                            @case('cash')
                                Наличными при получении
                                @break
                            @case('online')
                                Онлайн оплата картой
                                @break
                            @case('installment')
                                Рассрочка
                                @break
                        @endswitch
                    </span>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <iconify-icon icon="solar:bag-4-linear" width="20"></iconify-icon>
                Состав заказа
            </h2>
            <div class="space-y-4">
                @foreach($order->items as $item)
                <div class="flex gap-4 py-3 border-b border-gray-100 last:border-b-0">
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-gray-900">{{ $item->product_name }}</h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Количество: {{ $item->quantity }} × {{ number_format($item->price / 100, 0, ',', ' ') }} сом
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">
                            {{ number_format($item->subtotal / 100, 0, ',', ' ') }} сом
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Next Steps -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-3 flex items-center gap-2">
            <iconify-icon icon="solar:info-circle-linear" width="20"></iconify-icon>
            Что дальше?
        </h3>
        <p class="text-blue-800 text-sm leading-relaxed">
            Мы свяжемся с вами в ближайшее время для подтверждения заказа.
            Обычно это занимает от 15 минут до 1 часа в рабочее время.
            Пожалуйста, держите телефон под рукой.
        </p>
    </div>

    <!-- Actions -->
    <div class="flex justify-center">
        <a
            href="{{ route('products.index') }}"
            class="inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold px-6 py-3 rounded-lg transition-colors"
        >
            <iconify-icon icon="solar:arrow-left-linear" width="18"></iconify-icon>
            Вернуться к покупкам
        </a>
    </div>
</div>
@endsection
