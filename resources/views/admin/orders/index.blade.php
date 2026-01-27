@extends('layouts.admin')

@section('page-title', 'Заказы')

@section('content')
<div class="bg-white rounded-lg shadow">
    @if($orders->count() > 0)
        <!-- Orders Table - Desktop -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Номер заказа</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Клиент</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Телефон</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Сумма</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($orders as $order)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                    {{ $order->order_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $order->customer_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $order->customer_phone }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ number_format($order->total / 100, 0, ',', ' ') }} сом</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'new' => 'bg-blue-100 text-blue-800',
                                        'processing' => 'bg-yellow-100 text-yellow-800',
                                        'delivering' => 'bg-orange-100 text-orange-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                    ];
                                    $statusLabels = App\Models\Order::statusLabels();
                                @endphp
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabels[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $order->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                    Просмотреть
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Orders Cards - Mobile -->
        <div class="md:hidden divide-y divide-gray-200">
            @foreach($orders as $order)
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between mb-2">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                            {{ $order->order_number }}
                        </a>
                        @php
                            $statusColors = [
                                'new' => 'bg-blue-100 text-blue-800',
                                'processing' => 'bg-yellow-100 text-yellow-800',
                                'delivering' => 'bg-orange-100 text-orange-800',
                                'completed' => 'bg-green-100 text-green-800',
                            ];
                            $statusLabels = App\Models\Order::statusLabels();
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $statusLabels[$order->status] ?? $order->status }}
                        </span>
                    </div>
                    <div class="space-y-1 text-sm text-gray-600">
                        <div><strong>Клиент:</strong> {{ $order->customer_name }}</div>
                        <div><strong>Телефон:</strong> {{ $order->customer_phone }}</div>
                        <div><strong>Сумма:</strong> <span class="font-semibold text-gray-900">{{ number_format($order->total / 100, 0, ',', ' ') }} сом</span></div>
                        <div><strong>Дата:</strong> {{ $order->created_at->format('d.m.Y H:i') }}</div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Просмотреть детали →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $orders->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="px-6 py-12 text-center">
            <iconify-icon icon="solar:box-linear" width="64" class="text-gray-300 mx-auto mb-4"></iconify-icon>
            <h3 class="text-lg font-medium text-gray-900 mb-1">Заказов пока нет</h3>
            <p class="text-sm text-gray-500">Когда клиенты начнут делать заказы, они появятся здесь.</p>
        </div>
    @endif
</div>
@endsection
