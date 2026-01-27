@extends('layouts.admin')

@section('title', 'Отзывы')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Отзывы</h1>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-500 text-sm font-medium mb-2">Всего отзывов</h3>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['total'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-500 text-sm font-medium mb-2">На модерации</h3>
            <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-500 text-sm font-medium mb-2">Одобрено</h3>
            <p class="text-3xl font-bold text-green-600">{{ $stats['approved'] }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Поиск</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Товар или клиент..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Статус</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Все</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>На модерации</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Одобрено</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Рейтинг</label>
                <select name="rating" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Все</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} ⭐</option>
                    @endfor
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Применить
                </button>
            </div>
        </form>
    </div>

    <!-- Reviews List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="divide-y divide-gray-200">
            @forelse($reviews as $review)
                <div class="p-6 hover:bg-gray-50">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                @endfor
                                <span class="ml-2 text-sm text-gray-500">{{ $review->created_at->format('d.m.Y H:i') }}</span>
                            </div>

                            <h3 class="font-semibold text-lg mb-1">
                                <a href="{{ route('admin.products.show', $review->product) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $review->product->name }}
                                </a>
                            </h3>

                            @if($review->title)
                                <h4 class="font-medium mb-2">{{ $review->title }}</h4>
                            @endif

                            <p class="text-gray-700 mb-3">{{ $review->comment }}</p>

                            <div class="flex items-center text-sm text-gray-500 space-x-4">
                                <span>{{ $review->customer_name }}</span>
                                <span>{{ $review->customer_email }}</span>
                                @if($review->is_verified_purchase)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        Проверенная покупка
                                    </span>
                                @endif
                            </div>

                            @if($review->admin_response)
                                <div class="mt-4 pl-4 border-l-4 border-blue-500 bg-blue-50 p-3 rounded">
                                    <p class="text-sm font-medium text-blue-900 mb-1">Ответ администратора:</p>
                                    <p class="text-sm text-blue-800">{{ $review->admin_response }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="ml-4 flex flex-col items-end space-y-2">
                            @if($review->is_approved)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Одобрено
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    На модерации
                                </span>
                            @endif

                            <a href="{{ route('admin.reviews.show', $review) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                Подробнее →
                            </a>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="flex items-center space-x-3 mt-3">
                        @if(!$review->is_approved)
                            <form action="{{ route('admin.reviews.update', $review) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_approved" value="1">
                                <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                    Одобрить
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.reviews.update', $review) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_approved" value="0">
                                <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                                    Отклонить
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="inline" onsubmit="return confirm('Удалить отзыв?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                Удалить
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">
                    Отзывов не найдено
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($reviews->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
