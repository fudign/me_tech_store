@extends('layouts.admin')

@section('title', 'Отзыв #' . $review->id)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.reviews.index') }}" class="text-blue-600 hover:text-blue-800">
            ← Назад к списку отзывов
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Review Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Main Review Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-6 h-6 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        @endfor
                    </div>

                    @if($review->is_approved)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            Одобрено
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            На модерации
                        </span>
                    @endif
                </div>

                @if($review->title)
                    <h2 class="text-2xl font-bold mb-4">{{ $review->title }}</h2>
                @endif

                <p class="text-gray-700 text-lg mb-4">{{ $review->comment }}</p>

                <div class="border-t pt-4 mt-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Клиент:</span>
                            <p class="font-medium">{{ $review->customer_name }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Email:</span>
                            <p class="font-medium">{{ $review->customer_email }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Дата:</span>
                            <p class="font-medium">{{ $review->created_at->format('d.m.Y H:i') }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Покупка:</span>
                            @if($review->is_verified_purchase)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    Проверенная
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    Не проверена
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Response -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Ответ администратора</h3>

                @if($review->admin_response)
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                        <p class="text-blue-900">{{ $review->admin_response }}</p>
                        <p class="text-xs text-blue-600 mt-2">
                            Отправлено: {{ $review->admin_response_at->format('d.m.Y H:i') }}
                        </p>
                    </div>
                @endif

                <form action="{{ route('admin.reviews.update', $review) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <textarea name="admin_response"
                                  rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Напишите ответ на отзыв...">{{ old('admin_response', $review->admin_response) }}</textarea>
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Сохранить ответ
                    </button>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Product Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Товар</h3>

                <div class="flex items-start">
                    @if($review->product->primary_image_url)
                        <img src="{{ $review->product->primary_image_url }}"
                             alt="{{ $review->product->name }}"
                             class="w-20 h-20 object-cover rounded mr-4">
                    @endif

                    <div>
                        <h4 class="font-medium mb-1">
                            <a href="{{ route('admin.products.show', $review->product) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $review->product->name }}
                            </a>
                        </h4>
                        <p class="text-sm text-gray-500">{{ $review->product->sku }}</p>
                        <p class="text-sm font-semibold mt-2">
                            {{ number_format($review->product->price / 100, 0, ',', ' ') }} сом
                        </p>
                    </div>
                </div>

                <a href="{{ route('product.show', $review->product) }}" target="_blank" class="block mt-4 text-sm text-blue-600 hover:text-blue-800">
                    Открыть на сайте →
                </a>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Действия</h3>

                <div class="space-y-3">
                    @if(!$review->is_approved)
                        <form action="{{ route('admin.reviews.update', $review) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="is_approved" value="1">
                            <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                Одобрить отзыв
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.reviews.update', $review) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="is_approved" value="0">
                            <button type="submit" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700">
                                Отклонить отзыв
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Удалить отзыв безвозвратно?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            Удалить отзыв
                        </button>
                    </form>
                </div>
            </div>

            <!-- User Info (if registered) -->
            @if($review->user)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4">Зарегистрированный пользователь</h3>

                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="text-gray-500">Имя:</span>
                            <p class="font-medium">{{ $review->user->name }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Email:</span>
                            <p class="font-medium">{{ $review->user->email }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Зарегистрирован:</span>
                            <p class="font-medium">{{ $review->user->created_at->format('d.m.Y') }}</p>
                        </div>
                    </div>

                    <a href="{{ route('admin.customers.show', $review->user) }}" class="block mt-4 text-sm text-blue-600 hover:text-blue-800">
                        Профиль пользователя →
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
