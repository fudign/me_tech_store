@extends('layouts.admin')

@section('title', 'Редактировать категорию - Админ панель')
@section('page-title', 'Редактировать категорию')

@section('content')
<div class="max-w-3xl">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('admin.categories.index') }}" class="hover:text-gray-700">Категории</a>
            <iconify-icon icon="solar:alt-arrow-right-linear" width="16"></iconify-icon>
            <span class="text-gray-900">{{ $category->name }}</span>
        </nav>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('admin.categories.update', $category) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Название <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $category->name) }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                        placeholder="Например: Смартфоны"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                        URL (slug)
                    </label>
                    <input
                        type="text"
                        id="slug"
                        name="slug"
                        value="{{ old('slug', $category->slug) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('slug') border-red-500 @enderror"
                        placeholder="Оставьте пустым для автогенерации"
                    >
                    <p class="mt-1 text-xs text-gray-500">Если оставить пустым, будет создан автоматически из названия</p>
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Описание
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                        placeholder="Краткое описание категории"
                    >{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            name="is_active"
                            {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        >
                        <div>
                            <span class="text-sm font-medium text-gray-700">Активная категория</span>
                            <p class="text-xs text-gray-500">Отображается на сайте</p>
                        </div>
                    </label>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-200"></div>

                <!-- SEO Section -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <iconify-icon icon="solar:chart-linear" width="18"></iconify-icon>
                        SEO настройки
                    </h3>

                    <div class="space-y-4">
                        <!-- Meta Title -->
                        <div>
                            <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">
                                Meta Title
                            </label>
                            <input
                                type="text"
                                id="meta_title"
                                name="meta_title"
                                value="{{ old('meta_title', $category->meta_title) }}"
                                maxlength="60"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('meta_title') border-red-500 @enderror"
                                placeholder="Оставьте пустым для автогенерации"
                            >
                            <p class="mt-1 text-xs text-gray-500">Оптимально: 50-60 символов. Авто: {{ $category->name }} Xiaomi - купить в Бишкеке</p>
                            @error('meta_title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Meta Description -->
                        <div>
                            <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">
                                Meta Description
                            </label>
                            <textarea
                                id="meta_description"
                                name="meta_description"
                                rows="3"
                                maxlength="160"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('meta_description') border-red-500 @enderror"
                                placeholder="Оставьте пустым для автогенерации"
                            >{{ old('meta_description', $category->meta_description) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Оптимально: 150-160 символов</p>
                            @error('meta_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- URL Slug -->
                        <div>
                            <label for="seo_slug_display" class="block text-sm font-medium text-gray-700 mb-2">
                                URL Slug
                            </label>
                            <input
                                type="text"
                                id="seo_slug_display"
                                value="{{ $category->slug }}"
                                readonly
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600"
                            >
                            <p class="mt-1 text-xs text-gray-500">Генерируется автоматически из названия категории</p>
                        </div>

                        <!-- SERP Preview -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Превью в Google
                            </label>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-2">
                                <div class="text-sm text-blue-600 truncate" style="max-width: 600px;">
                                    {{ $category->meta_title ?: $category->name . ' Xiaomi - купить в Бишкеке | Mi Tech' }}
                                </div>
                                <div class="text-xs text-green-700">
                                    {{ config('app.url') }}/categories/{{ $category->slug }}
                                </div>
                                <div class="text-sm text-gray-600 line-clamp-2" style="max-width: 600px;">
                                    {{ $category->meta_description ?: 'Купить ' . $category->name . ' Xiaomi в Бишкеке. Большой выбор, официальная гарантия, доставка по Кыргызстану.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg flex items-center justify-end gap-3">
                <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Отмена
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    Сохранить изменения
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
