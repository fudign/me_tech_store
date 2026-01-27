@extends('layouts.admin')

@section('page-title', 'Заказ ' . $order->order_number)

@section('content')
<div class="max-w-6xl">
    <!-- Back Link -->
    <div class="mb-6">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
            <iconify-icon icon="solar:alt-arrow-left-linear" width="20"></iconify-icon>
            <span>Все заказы</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Order Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Details Card -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Заказ {{ $order->order_number }}</h2>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Дата заказа:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $order->created_at->format('d.m.Y в H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Статус:</span>
                        @php
                            $statusColors = [
                                'new' => 'bg-blue-100 text-blue-800',
                                'processing' => 'bg-yellow-100 text-yellow-800',
                                'delivering' => 'bg-orange-100 text-orange-800',
                                'completed' => 'bg-green-100 text-green-800',
                            ];
                            $statusLabels = App\Models\Order::statusLabels();
                        @endphp
                        <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $statusLabels[$order->status] ?? $order->status }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Способ оплаты:</span>
                        <span class="text-sm font-medium text-gray-900">
                            @if($order->payment_method === 'cash')
                                Наличными при получении
                            @elseif($order->payment_method === 'online')
                                Онлайн оплата картой
                            @elseif($order->payment_method === 'installment')
                                Рассрочка
                            @else
                                {{ $order->payment_method }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Order Items Card -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Товары в заказе</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Товар</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Количество</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            @if($item->product_slug)
                                                <a href="{{ route('product.show', $item->product_slug) }}" target="_blank" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                                    {{ $item->product_name }}
                                                </a>
                                            @else
                                                <span class="text-sm font-medium text-gray-900">{{ $item->product_name }}</span>
                                            @endif
                                            @if($item->attributes && is_array(json_decode($item->attributes, true)))
                                                <div class="mt-1 flex flex-wrap gap-2">
                                                    @foreach(json_decode($item->attributes, true) as $key => $value)
                                                        <span class="text-xs text-gray-500">{{ $key }}: {{ $value }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ number_format($item->price / 100, 0, ',', ' ') }} сом</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $item->quantity }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900">{{ number_format($item->subtotal / 100, 0, ',', ' ') }} сом</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Подытог:</span>
                        <span class="text-gray-900">{{ number_format($order->subtotal / 100, 0, ',', ' ') }} сом</span>
                    </div>
                    <div class="flex items-center justify-between text-base font-semibold">
                        <span class="text-gray-900">Итого:</span>
                        <span class="text-gray-900 text-lg">{{ number_format($order->total / 100, 0, ',', ' ') }} сом</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Customer & Status Update -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Customer Information Card -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Информация о клиенте</h2>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Имя</div>
                        <div class="text-sm font-medium text-gray-900">{{ $order->customer_name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Телефон</div>
                        <a href="tel:{{ $order->customer_phone }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                            {{ $order->customer_phone }}
                        </a>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 mb-1">Адрес доставки</div>
                        <div class="text-sm text-gray-900 whitespace-pre-line">{{ $order->customer_address }}</div>
                    </div>
                </div>
            </div>

            <!-- Status Update Card -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Изменить статус заказа</h2>
                </div>
                <div class="px-6 py-4">
                    <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Статус</label>
                                <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    @foreach(App\Models\Order::statusLabels() as $value => $label)
                                        <option value="{{ $value }}" {{ $order->status === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors">
                                Обновить статус
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
