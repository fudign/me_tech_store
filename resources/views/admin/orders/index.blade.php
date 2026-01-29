@extends('layouts.admin')

@section('page-title', 'Заказы')

@section('content')
<!-- Search and Filters -->
<div class="bg-white rounded-lg shadow mb-6 p-4">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="space-y-4 md:space-y-0 md:flex md:items-end md:gap-4">
        <!-- Search -->
        <div class="flex-1">
            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Поиск</label>
            <input
                type="text"
                name="search"
                id="search"
                value="{{ request('search') }}"
                placeholder="Номер заказа, имя или телефон"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
        </div>

        <!-- Status Filter -->
        <div class="w-full md:w-48">
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Статус</label>
            <select
                name="status"
                id="status"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">Все статусы</option>
                @foreach(App\Models\Order::statusLabels() as $value => $label)
                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Buttons -->
        <div class="flex gap-2">
            <button
                type="submit"
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
            >
                Поиск
            </button>
            @if(request('search') || request('status'))
                <a
                    href="{{ route('admin.orders.index') }}"
                    class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors"
                >
                    Сбросить
                </a>
            @endif
        </div>
    </form>

    <!-- Status Counts -->
    <div class="mt-4 pt-4 border-t border-gray-200 flex flex-wrap gap-2">
        <a href="{{ route('admin.orders.index') }}"
           class="px-3 py-1.5 text-sm rounded-full transition-colors {{ !request('status') ? 'bg-blue-100 text-blue-800 font-semibold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Все ({{ $statusCounts['all'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'new']) }}"
           class="px-3 py-1.5 text-sm rounded-full transition-colors {{ request('status') === 'new' ? 'bg-blue-100 text-blue-800 font-semibold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Новые ({{ $statusCounts['new'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}"
           class="px-3 py-1.5 text-sm rounded-full transition-colors {{ request('status') === 'processing' ? 'bg-yellow-100 text-yellow-800 font-semibold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            В обработке ({{ $statusCounts['processing'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'delivering']) }}"
           class="px-3 py-1.5 text-sm rounded-full transition-colors {{ request('status') === 'delivering' ? 'bg-orange-100 text-orange-800 font-semibold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Доставляется ({{ $statusCounts['delivering'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}"
           class="px-3 py-1.5 text-sm rounded-full transition-colors {{ request('status') === 'completed' ? 'bg-green-100 text-green-800 font-semibold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Выполнено ({{ $statusCounts['completed'] }})
        </a>
    </div>
</div>

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
