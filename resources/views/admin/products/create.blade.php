@extends('layouts.admin')

@section('title', 'Добавить товар')

@section('content')
<div class="max-w-5xl">
    <!-- Validation Errors -->
    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <div class="flex items-center gap-2 mb-2">
                <iconify-icon icon="solar:danger-triangle-linear" width="20"></iconify-icon>
                <span class="font-semibold">Ошибки валидации:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div x-data="productForm()" class="bg-white rounded-lg shadow-sm">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button @click="tab = 'basic'"
                        type="button"
                        :class="tab === 'basic' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
                    Основное
                </button>
                <button @click="tab = 'images'"
                        type="button"
                        :class="tab === 'images' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
                    Фото
                </button>
                <button @click="tab = 'attributes'"
                        type="button"
                        :class="tab === 'attributes' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
                    Характеристики
                </button>
                <button @click="tab = 'seo'"
                        type="button"
                        :class="tab === 'seo' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
                    SEO
                </button>
            </nav>
        </div>

        <!-- Form -->
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Tab 1: Basic Info -->
                <div x-show="tab === 'basic'" x-cloak>
                    <div class="space-y-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Название <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name') }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Slug -->
                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                                Slug (URL)
                            </label>
                            <input type="text"
                                   name="slug"
                                   id="slug"
                                   value="{{ old('slug') }}"
                                   placeholder="Генерируется автоматически"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Оставьте пустым для автоматической генерации из названия</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Описание
                            </label>
                            <textarea name="description"
                                      id="description"
                                      rows="4"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                        </div>

                        <!-- Price -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                    Цена <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number"
                                           name="price"
                                           id="price"
                                           value="{{ old('price') }}"
                                           step="1"
                                           min="0"
                                           required
                                           class="w-full px-4 py-2 pr-16 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">сом</span>
                                </div>
                            </div>

                            <div>
                                <label for="old_price" class="block text-sm font-medium text-gray-700 mb-2">
                                    Акционная цена (старая)
                                </label>
                                <div class="relative">
                                    <input type="number"
                                           name="old_price"
                                           id="old_price"
                                           value="{{ old('old_price') }}"
                                           step="1"
                                           min="0"
                                           class="w-full px-4 py-2 pr-16 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">сом</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Будет зачеркнута рядом с новой ценой</p>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Категории
                            </label>
                            <div class="space-y-2">
                                @foreach($categories as $category)
                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               name="categories[]"
                                               value="{{ $category->id }}"
                                               {{ in_array($category->id, old('categories', [])) ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">{{ $category->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Availability Status -->
                        <div>
                            <label for="availability_status" class="block text-sm font-medium text-gray-700 mb-2">
                                Наличие товара <span class="text-red-500">*</span>
                            </label>
                            <select name="availability_status"
                                    id="availability_status"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="in_stock" {{ old('availability_status') == 'in_stock' ? 'selected' : '' }}>В наличии</option>
                                <option value="out_of_stock" {{ old('availability_status') == 'out_of_stock' ? 'selected' : '' }}>Нет в наличии</option>
                                <option value="coming_soon" {{ old('availability_status') == 'coming_soon' ? 'selected' : '' }}>Скоро будет</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Выберите статус наличия товара на складе</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox"
                                       name="is_active"
                                       {{ old('is_active', true) ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Опубликован</span>
                            </label>
                            <p class="mt-1 text-xs text-gray-500">Если не отмечено, товар будет в статусе "Черновик" и не будет виден на сайте</p>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Images -->
                <div x-show="tab === 'images'" x-cloak>
                    <div class="space-y-6">
                        <!-- Image Upload -->
                        <div>
                            <label for="images" class="block text-sm font-medium text-gray-700 mb-2">
                                Фотографии товара
                            </label>

                            <!-- Info Box -->
                            <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex gap-3">
                                    <iconify-icon icon="solar:info-circle-linear" width="20" class="text-blue-600 flex-shrink-0 mt-0.5"></iconify-icon>
                                    <div class="text-sm text-blue-900">
                                        <p class="font-medium mb-1">Требования к изображениям:</p>
                                        <ul class="list-disc list-inside space-y-1 text-blue-800">
                                            <li>Форматы: <strong>JPG, PNG, WEBP</strong></li>
                                            <li>Максимальный размер: <strong>2 МБ</strong> на файл</li>
                                            <li>Рекомендуемое разрешение: <strong>1200x1200 пикселей</strong></li>
                                            <li>Максимум <strong>10 изображений</strong> на товар</li>
                                            <li>Первое фото будет <strong>главным</strong></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors">
                                <div class="space-y-1 text-center">
                                    <iconify-icon icon="solar:gallery-add-linear" width="48" class="mx-auto text-gray-400"></iconify-icon>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="images" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                            <span>Выберите файлы</span>
                                            <input id="images"
                                                   name="images[]"
                                                   type="file"
                                                   multiple
                                                   accept="image/jpeg,image/png,image/webp"
                                                   @change="previewImages"
                                                   class="sr-only">
                                        </label>
                                        <p class="pl-1">или перетащите сюда</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        PNG, JPG, WEBP до 2MB
                                    </p>
                                </div>
                            </div>

                            <!-- Image Previews -->
                            <div x-show="imagePreviews.length > 0" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                                <template x-for="(preview, index) in imagePreviews" :key="index">
                                    <div class="relative group">
                                        <img :src="preview"
                                             :alt="'Preview ' + (index + 1)"
                                             class="w-full h-32 object-cover rounded-lg border-2 border-gray-200">
                                        <div class="absolute top-2 left-2" x-show="index === 0">
                                            <span class="bg-blue-600 text-white text-xs px-2 py-1 rounded font-medium">Главное</span>
                                        </div>
                                        <div class="absolute top-2 right-2">
                                            <span class="bg-gray-900 bg-opacity-70 text-white text-xs px-2 py-1 rounded" x-text="index + 1"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Attributes -->
                <div x-show="tab === 'attributes'" x-cloak>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Характеристики товара
                            </label>
                            <p class="text-sm text-gray-500 mb-4">
                                Добавьте динамические характеристики (например, Память: 256GB, Цвет: Черный)
                            </p>

                            <div class="space-y-3">
                                <template x-for="(attr, index) in attributes" :key="index">
                                    <div class="flex gap-2">
                                        <input type="text"
                                               x-model="attr.key"
                                               :name="'attributes['+index+'][key]'"
                                               placeholder="Название (Память, Цвет...)"
                                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <input type="text"
                                               x-model="attr.value"
                                               :name="'attributes['+index+'][value]'"
                                               placeholder="Значение (256GB, Черный...)"
                                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <button type="button"
                                                @click="removeAttribute(index)"
                                                class="px-3 py-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors">
                                            <iconify-icon icon="solar:trash-bin-trash-linear" width="20"></iconify-icon>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <button type="button"
                                    @click="addAttribute()"
                                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                <iconify-icon icon="solar:add-circle-linear" width="18"></iconify-icon>
                                Добавить характеристику
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tab 4: SEO -->
                <div x-show="tab === 'seo'" x-cloak>
                    <div class="space-y-6">
                        <!-- Meta Title -->
                        <div>
                            <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">
                                Meta Title
                            </label>
                            <input type="text"
                                   name="meta_title"
                                   id="meta_title"
                                   value="{{ old('meta_title') }}"
                                   maxlength="200"
                                   placeholder="Название товара (если пусто)"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Максимум 200 символов</p>
                        </div>

                        <!-- Meta Description -->
                        <div>
                            <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">
                                Meta Description
                            </label>
                            <textarea name="meta_description"
                                      id="meta_description"
                                      rows="3"
                                      maxlength="300"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('meta_description') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Максимум 300 символов</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                <a href="{{ route('admin.products.index') }}"
                   class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    Отмена
                </a>
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Сохранить
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

<script>
function productForm() {
    return {
        tab: 'basic',
        attributes: [{ key: '', value: '' }],
        imagePreviews: [],

        addAttribute() {
            this.attributes.push({ key: '', value: '' });
        },

        removeAttribute(index) {
            if (this.attributes.length > 1) {
                this.attributes.splice(index, 1);
            }
        },

        previewImages(event) {
            const files = event.target.files;
            this.imagePreviews = [];

            if (files.length > 10) {
                alert('Максимум 10 изображений!');
                event.target.value = '';
                return;
            }

            for (let i = 0; i < files.length; i++) {
                const file = files[i];

                // Check file size (2MB = 2097152 bytes)
                if (file.size > 2097152) {
                    alert(`Файл "${file.name}" слишком большой! Максимальный размер: 2 МБ`);
                    continue;
                }

                // Check file type
                if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                    alert(`Файл "${file.name}" имеет неподдерживаемый формат! Используйте JPG, PNG или WEBP`);
                    continue;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreviews.push(e.target.result);
                };
                reader.readAsDataURL(file);
            }
        }
    }
}
</script>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection
