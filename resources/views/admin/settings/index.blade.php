@extends('layouts.admin')

@section('title', 'Настройки сайта - Админ панель')
@section('page-title', 'Настройки сайта')

@section('content')
<div class="max-w-3xl">
    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <div class="p-6">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Контактная информация</h2>
                    <p class="text-sm text-gray-500 mt-1">Эти данные будут отображаться на сайте в футере и на странице контактов</p>
                </div>

                <div class="space-y-6">
                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Телефон <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $settings['phone']) }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror"
                            placeholder="+996 XXX XXX XXX"
                        >
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Телефон для связи с клиентами</p>
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            Адрес <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="address"
                            name="address"
                            value="{{ old('address', $settings['address']) }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-500 @enderror"
                            placeholder="г. Бишкек, ул. Примерная 123"
                        >
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Физический адрес магазина</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $settings['email']) }}"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                            placeholder="info@example.com"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Email для связи с клиентами</p>
                    </div>

                    <!-- WhatsApp -->
                    <div>
                        <label for="whatsapp" class="block text-sm font-medium text-gray-700 mb-2">
                            WhatsApp
                        </label>
                        <input
                            type="text"
                            id="whatsapp"
                            name="whatsapp"
                            value="{{ old('whatsapp', $settings['whatsapp']) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('whatsapp') border-red-500 @enderror"
                            placeholder="996555000000"
                        >
                        @error('whatsapp')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Номер WhatsApp в международном формате (без + и пробелов, например: 996555000000)</p>
                    </div>

                    <!-- Footer Text -->
                    <div>
                        <label for="footer_text" class="block text-sm font-medium text-gray-700 mb-2">
                            Текст в футере
                        </label>
                        <textarea
                            id="footer_text"
                            name="footer_text"
                            rows="3"
                            maxlength="500"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('footer_text') border-red-500 @enderror"
                            placeholder="Дополнительный текст для отображения в футере"
                        >{{ old('footer_text', $settings['footer_text']) }}</textarea>
                        @error('footer_text')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Дополнительная информация в подвале сайта (не обязательно)</p>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="p-6 border-t border-gray-200">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Карта магазина</h2>
                    <p class="text-sm text-gray-500 mt-1">Укажите координаты для отображения карты с меткой вашего магазина в футере</p>
                </div>

                <div class="space-y-6">
                    <!-- Map Coordinates -->
                    <div>
                        <label for="map_coordinates" class="block text-sm font-medium text-gray-700 mb-2">
                            Координаты магазина
                        </label>
                        <input
                            type="text"
                            id="map_coordinates"
                            name="map_coordinates"
                            value="{{ old('map_coordinates', ($settings['map_latitude'] && $settings['map_longitude']) ? $settings['map_latitude'] . ', ' . $settings['map_longitude'] : '') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('map_coordinates') border-red-500 @enderror"
                            placeholder="42.8746, 74.5698"
                        >
                        @error('map_coordinates')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Введите координаты в формате: широта, долгота (например: 42.8746, 74.5698)</p>
                    </div>

                    <!-- Help Text -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex gap-3">
                            <iconify-icon icon="solar:info-circle-linear" width="20" class="text-blue-600 flex-shrink-0 mt-0.5"></iconify-icon>
                            <div>
                                <p class="text-sm text-blue-900 font-medium mb-1">Как найти координаты?</p>
                                <p class="text-sm text-blue-800 leading-relaxed">
                                    <strong>Способ 1:</strong> Откройте <a href="https://2gis.kg/bishkek" target="_blank" class="underline font-medium">2GIS</a> → Найдите магазин → Нажмите правой кнопкой → "Что здесь?" → Скопируйте координаты<br>
                                    <strong>Способ 2:</strong> В адресной строке 2GIS будет URL типа <code class="bg-blue-100 px-1 rounded text-xs">m=74.5698,42.8746</code> — скопируйте эти числа
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Media & Footer Section -->
            <div class="p-6 border-t border-gray-200">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Социальные сети</h2>
                    <p class="text-sm text-gray-500 mt-1">Ссылки на социальные сети в футере сайта</p>
                </div>

                <div class="space-y-6">
                    <!-- Instagram -->
                    <div>
                        <label for="social_instagram" class="block text-sm font-medium text-gray-700 mb-2">
                            Instagram
                        </label>
                        <input
                            type="url"
                            id="social_instagram"
                            name="social_instagram"
                            value="{{ old('social_instagram', $settings['social_instagram'] ?? '') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('social_instagram') border-red-500 @enderror"
                            placeholder="https://instagram.com/your_account"
                        >
                        @error('social_instagram')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Ссылка на страницу в Instagram</p>
                    </div>

                    <!-- Facebook -->
                    <div>
                        <label for="social_facebook" class="block text-sm font-medium text-gray-700 mb-2">
                            Facebook
                        </label>
                        <input
                            type="url"
                            id="social_facebook"
                            name="social_facebook"
                            value="{{ old('social_facebook', $settings['social_facebook'] ?? '') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('social_facebook') border-red-500 @enderror"
                            placeholder="https://facebook.com/your_page"
                        >
                        @error('social_facebook')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Ссылка на страницу в Facebook</p>
                    </div>

                    <!-- YouTube -->
                    <div>
                        <label for="social_youtube" class="block text-sm font-medium text-gray-700 mb-2">
                            YouTube
                        </label>
                        <input
                            type="url"
                            id="social_youtube"
                            name="social_youtube"
                            value="{{ old('social_youtube', $settings['social_youtube'] ?? '') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('social_youtube') border-red-500 @enderror"
                            placeholder="https://youtube.com/@your_channel"
                        >
                        @error('social_youtube')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Ссылка на канал YouTube</p>
                    </div>
                </div>
            </div>

            <!-- Footer Blocks Section -->
            <div class="p-6 border-t border-gray-200">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Блоки в футере</h2>
                    <p class="text-sm text-gray-500 mt-1">Заголовки и описания для блоков информации в футере</p>
                </div>

                <div class="space-y-8">
                    <!-- Catalog Block -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-4">Блок "Каталог"</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="footer_catalog_title" class="block text-sm font-medium text-gray-700 mb-2">
                                    Заголовок
                                </label>
                                <input
                                    type="text"
                                    id="footer_catalog_title"
                                    name="footer_catalog_title"
                                    value="{{ old('footer_catalog_title', $settings['footer_catalog_title'] ?? 'Каталог') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Каталог"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Clients Block -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-4">Блок "Клиентам"</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="footer_clients_title" class="block text-sm font-medium text-gray-700 mb-2">
                                    Заголовок
                                </label>
                                <input
                                    type="text"
                                    id="footer_clients_title"
                                    name="footer_clients_title"
                                    value="{{ old('footer_clients_title', $settings['footer_clients_title'] ?? 'Клиентам') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Клиентам"
                                >
                            </div>
                            <div>
                                <label for="footer_clients_text" class="block text-sm font-medium text-gray-700 mb-2">
                                    Текст/Описание
                                </label>
                                <textarea
                                    id="footer_clients_text"
                                    name="footer_clients_text"
                                    rows="3"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Связаться с нами&#10;Гарантия&#10;Доставка"
                                >{{ old('footer_clients_text', $settings['footer_clients_text'] ?? "Связаться с нами\nГарантия\nДоставка") }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">Каждая строка - отдельный пункт</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contacts Block -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-4">Блок "Контакты"</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="footer_contacts_title" class="block text-sm font-medium text-gray-700 mb-2">
                                    Заголовок
                                </label>
                                <input
                                    type="text"
                                    id="footer_contacts_title"
                                    name="footer_contacts_title"
                                    value="{{ old('footer_contacts_title', $settings['footer_contacts_title'] ?? 'Контакты') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Контакты"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Contact Info Section -->
            <div class="p-6 border-t border-gray-200">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Контактная информация на странице товара</h2>
                    <p class="text-sm text-gray-500 mt-1">Этот текст будет отображаться на каждой странице товара</p>
                </div>

                <div class="space-y-6">
                    <div>
                        <label for="product_contact_info" class="block text-sm font-medium text-gray-700 mb-2">
                            Текст контактной информации
                        </label>
                        <textarea
                            id="product_contact_info"
                            name="product_contact_info"
                            rows="15"
                            maxlength="2000"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('product_contact_info') border-red-500 @enderror font-mono text-sm"
                            placeholder="Уточняйте наличие по телефонам:&#10;+996 700 916 121&#10;+996 551 916 122&#10;&#10;Адреса:&#10;Шопоково 123"
                        >{{ old('product_contact_info', $settings['product_contact_info'] ?? '') }}</textarea>
                        @error('product_contact_info')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Форматирование (переносы строк) будет сохранено при отображении</p>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex gap-3">
                            <iconify-icon icon="solar:info-circle-linear" width="20" class="text-blue-600 flex-shrink-0 mt-0.5"></iconify-icon>
                            <div>
                                <p class="text-sm text-blue-900 font-medium mb-1">Где отображается?</p>
                                <p class="text-sm text-blue-800 leading-relaxed">
                                    Этот текст будет показан на странице каждого товара в виде информационного блока с контактами и адресами магазинов.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hero Banner Section -->
            <div class="p-6 border-t border-gray-200">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Главный баннер (Hero Section)</h2>
                    <p class="text-sm text-gray-500 mt-1">Настройки большого баннера на главной странице</p>
                </div>

                <div class="space-y-6">
                    <!-- Badge Text -->
                    <div>
                        <label for="hero_badge" class="block text-sm font-medium text-gray-700 mb-2">
                            Текст бейджа
                        </label>
                        <input
                            type="text"
                            id="hero_badge"
                            name="hero_badge"
                            value="{{ old('hero_badge', $settings['hero_badge']) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('hero_badge') border-red-500 @enderror"
                            placeholder="Новинка"
                        >
                        @error('hero_badge')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Маленький бейдж вверху (например "Новинка", "Акция")</p>
                    </div>

                    <!-- Title -->
                    <div>
                        <label for="hero_title" class="block text-sm font-medium text-gray-700 mb-2">
                            Заголовок
                        </label>
                        <input
                            type="text"
                            id="hero_title"
                            name="hero_title"
                            value="{{ old('hero_title', $settings['hero_title']) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('hero_title') border-red-500 @enderror"
                            placeholder="Xiaomi 14 Ultra"
                        >
                        @error('hero_title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Основной заголовок баннера</p>
                    </div>

                    <!-- Subtitle -->
                    <div>
                        <label for="hero_subtitle" class="block text-sm font-medium text-gray-700 mb-2">
                            Подзаголовок
                        </label>
                        <input
                            type="text"
                            id="hero_subtitle"
                            name="hero_subtitle"
                            value="{{ old('hero_subtitle', $settings['hero_subtitle']) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('hero_subtitle') border-red-500 @enderror"
                            placeholder="Оптика Leica."
                        >
                        @error('hero_subtitle')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Подзаголовок (вторая строка)</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="hero_description" class="block text-sm font-medium text-gray-700 mb-2">
                            Описание
                        </label>
                        <textarea
                            id="hero_description"
                            name="hero_description"
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('hero_description') border-red-500 @enderror"
                            placeholder="Легендарная оптика, процессор Snapdragon 8 Gen 3..."
                        >{{ old('hero_description', $settings['hero_description']) }}</textarea>
                        @error('hero_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Описание товара под заголовком</p>
                    </div>

                    <!-- Image URL -->
                    <div>
                        <label for="hero_image_url" class="block text-sm font-medium text-gray-700 mb-2">
                            URL картинки
                        </label>
                        <input
                            type="url"
                            id="hero_image_url"
                            name="hero_image_url"
                            value="{{ old('hero_image_url', $settings['hero_image_url']) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('hero_image_url') border-red-500 @enderror"
                            placeholder="https://example.com/image.jpg"
                        >
                        @error('hero_image_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Полный URL изображения для баннера</p>
                    </div>

                    <!-- Product Selection -->
                    <div>
                        <label for="hero_product_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Товар для ссылок
                        </label>
                        <select
                            id="hero_product_id"
                            name="hero_product_id"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('hero_product_id') border-red-500 @enderror"
                        >
                            <option value="">-- Не выбрано (ссылки на каталог) --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('hero_product_id', $settings['hero_product_id']) == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('hero_product_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Выберите товар, на который будут вести кнопки "Купить" и "Подробнее"</p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg flex items-center justify-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center gap-2">
                    <iconify-icon icon="solar:diskette-linear" width="20"></iconify-icon>
                    <span>Сохранить настройки</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Info Box -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex gap-3">
            <iconify-icon icon="solar:info-circle-linear" width="20" class="text-blue-600 flex-shrink-0 mt-0.5"></iconify-icon>
            <div>
                <p class="text-sm text-blue-900 font-medium">Как использовать настройки</p>
                <p class="text-sm text-blue-800 mt-1">Эти настройки можно использовать в шаблонах через <code class="bg-blue-100 px-1 py-0.5 rounded text-xs">Setting::get('site.phone')</code></p>
            </div>
        </div>
    </div>
</div>
@endsection
