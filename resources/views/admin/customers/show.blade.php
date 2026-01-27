@extends('layouts.admin')

@section('title', 'Клиент: ' . $customer->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.customers.index') }}" class="text-blue-600 hover:text-blue-800">
            ← Назад к списку клиентов
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Customer Info Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <div class="h-16 w-16 rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl font-semibold mr-4">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold">{{ $customer->name }}</h2>
                    @if($customer->email_verified_at)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                            Подтверждён
                        </span>
                    @endif
                </div>
            </div>

            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-gray-500">Email:</span>
                    <p class="font-medium">{{ $customer->email }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Телефон:</span>
                    <p class="font-medium">{{ $customer->phone ?? 'Не указан' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Дата регистрации:</span>
                    <p class="font-medium">{{ $customer->created_at->format('d.m.Y H:i') }}</p>
                </div>
                @if($customer->last_login_at)
                    <div>
                        <span class="text-gray-500">Последний вход:</span>
                        <p class="font-medium">{{ $customer->last_login_at->format('d.m.Y H:i') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium mb-2">Всего заказов</h3>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['total_orders'] }}</p>
                <div class="mt-2 flex items-center text-sm">
                    <span class="text-green-600">{{ $stats['completed_orders'] }} выполнено</span>
                    @if($stats['pending_orders'] > 0)
                        <span class="text-gray-400 mx-2">•</span>
                        <span class="text-yellow-600">{{ $stats['pending_orders'] }} в обработке</span>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium mb-2">Потрачено всего</h3>
                <p class="text-3xl font-bold text-gray-800">
                    {{ number_format($stats['total_spent'] / 100, 0, ',', ' ') }} сом
                </p>
                <p class="text-sm text-gray-500 mt-2">
                    Средний чек: {{ number_format($stats['average_order'] / 100, 0, ',', ' ') }} сом
                </p>
            </div>
        </div>
    </div>

    <!-- Orders History -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold">История заказов</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Номер</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Товары</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customer->orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                #{{ $order->order_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $order->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="max-w-xs">
                                    @foreach($order->items as $item)
                                        <div class="truncate">{{ $item->product_name }} × {{ $item->quantity }}</div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($order->total / 100, 0, ',', ' ') }} сом
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($order->status === 'new') bg-blue-100 text-blue-800
                                    @elseif($order->status === 'processing') bg-yellow-100 text-yellow-800
                                    @elseif($order->status === 'delivering') bg-purple-100 text-purple-800
                                    @else bg-green-100 text-green-800 @endif">
                                    {{ \App\Models\Order::statusLabels()[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-900">
                                    Подробнее
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                У клиента пока нет заказов
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
