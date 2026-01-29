<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mi Tech | Магазин электроники')</title>

    @if(isset($metaDescription) || View::hasSection('meta_description'))
        <meta name="description" content="@yield('meta_description', $metaDescription ?? '')">
    @endif

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    {!! JsonLd::generate() !!}

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            500: '#f97316', // Xiaomi Orange-ish
                            600: '#ea580c',
                            900: '#171717', // Neutral Black
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased selection:bg-brand-500 selection:text-white">

    <!-- Top Bar -->
    <div class="bg-white border-b border-gray-100 text-xs text-gray-500">
        <div class="max-w-7xl mx-auto px-6 h-10 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5 hover:text-gray-900 cursor-pointer transition-colors">
                    <iconify-icon icon="solar:phone-calling-linear" stroke-width="1.5"></iconify-icon>
                    {{ \App\Models\Setting::get('site.phone', '+996 (555) 00-00-00') }}
                </span>
                <span class="hidden sm:flex items-center gap-1.5 hover:text-gray-900 cursor-pointer transition-colors">
                    <iconify-icon icon="solar:map-point-linear" stroke-width="1.5"></iconify-icon>
                    {{ \App\Models\Setting::get('site.address', 'Бишкек, пр. Манаса 101') }}
                </span>
            </div>
            <div class="flex items-center gap-4">
                <a href="#" class="hover:text-gray-900 transition-colors">Поддержка</a>
                <a href="#" class="hover:text-gray-900 transition-colors">Гарантия</a>
                <div class="w-px h-3 bg-gray-200"></div>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-1 hover:text-brand-500 transition-colors font-medium">
                    <iconify-icon icon="solar:settings-linear" width="14" stroke-width="2"></iconify-icon>
                    Админ-панель
                </a>
                <div class="w-px h-3 bg-gray-200"></div>
                <div class="flex items-center gap-2 cursor-pointer hover:text-gray-900">
                    <span>Русский</span>
                    <iconify-icon icon="solar:alt-arrow-down-linear"></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    <!-- Header / Navigation -->
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-6 h-16 md:h-20 flex items-center justify-between gap-8">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                <div class="w-8 h-8 bg-brand-500 rounded-lg flex items-center justify-center text-white text-lg font-semibold group-hover:scale-105 transition-transform duration-300">
                    mi
                </div>
                <span class="text-xl tracking-tight font-semibold text-gray-900">Mi Tech</span>
            </a>

            <!-- Search -->
            <form action="{{ route('search') }}" method="GET"
                  class="hidden md:flex flex-1 max-w-lg relative group"
                  x-data="searchAutocomplete()"
                  @click.outside="showResults = false">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-brand-500 transition-colors">
                    <iconify-icon icon="solar:magnifer-linear" width="20" stroke-width="1.5"></iconify-icon>
                </div>
                <input type="text"
                       name="q"
                       x-model="query"
                       @input.debounce.300ms="search"
                       @focus="showResults = true"
                       value="{{ request('q') }}"
                       placeholder="Поиск товаров (например, Xiaomi 14)..."
                       class="w-full bg-gray-50 border border-gray-200 rounded-full py-2.5 pl-10 pr-4 text-sm outline-none focus:ring-2 focus:ring-brand-100 focus:border-brand-500 transition-all placeholder:text-gray-400"
                       maxlength="200"
                       autocomplete="off">

                <!-- Dropdown results -->
                <div x-show="showResults && results.length > 0"
                     x-transition
                     class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden">
                    <template x-for="product in results" :key="product.id">
                        <a :href="product.url"
                           class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors">
                            <img :src="product.image || '/placeholder.png'"
                                 :alt="product.name"
                                 class="w-12 h-12 object-cover rounded">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900 truncate" x-text="product.name"></div>
                                <div class="text-xs text-gray-500" x-text="product.price"></div>
                            </div>
                        </a>
                    </template>
                </div>
            </form>

            <!-- Actions -->
            <div class="flex items-center gap-2 md:gap-4" x-data="{ cartCount: {{ Cart::getTotalQuantity() }}, wishlistCount: {{ count(session('wishlist', [])) }} }" @cart-updated.window="cartCount = $event.detail.count" @wishlist-updated.window="wishlistCount = $event.detail.count">
                <a href="{{ route('wishlist.index') }}" class="p-2 text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-full transition-all relative">
                    <iconify-icon icon="solar:heart-linear" width="24" stroke-width="1.5"></iconify-icon>
                    <span x-show="wishlistCount > 0"
                          x-text="wishlistCount"
                          class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center font-bold"></span>
                </a>
                <a href="{{ route('cart.index') }}" class="flex items-center gap-2 pl-2 pr-4 py-1.5 bg-gray-900 text-white rounded-full hover:bg-gray-800 transition-all active:scale-95 group">
                    <div class="relative">
                        <iconify-icon icon="solar:cart-large-minimalistic-linear" width="20" stroke-width="1.5"></iconify-icon>
                        <span x-show="cartCount > 0" x-text="cartCount" class="absolute -top-1 -right-1 bg-brand-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center font-bold"></span>
                    </div>
                    <span class="text-sm font-medium cart-count">Корзина</span>
                </a>
                <button class="md:hidden p-2 text-gray-900">
                    <iconify-icon icon="solar:hamburger-menu-linear" width="24" stroke-width="1.5"></iconify-icon>
                </button>
            </div>
        </div>

        <!-- Mobile Search (Visible only on mobile) -->
        <form action="{{ route('search') }}" method="GET"
              class="md:hidden px-6 pb-4 relative"
              x-data="searchAutocomplete()"
              @click.outside="showResults = false">
            <div class="relative">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <iconify-icon icon="solar:magnifer-linear" width="18"></iconify-icon>
                </div>
                <input type="text"
                       name="q"
                       x-model="query"
                       @input.debounce.300ms="search"
                       @focus="showResults = true"
                       value="{{ request('q') }}"
                       placeholder="Поиск..."
                       class="w-full bg-gray-50 border border-gray-200 rounded-lg py-2 pl-9 text-sm focus:border-brand-500 outline-none"
                       maxlength="200"
                       autocomplete="off">
            </div>

            <!-- Dropdown results -->
            <div x-show="showResults && results.length > 0"
                 x-transition
                 class="absolute left-6 right-6 mt-2 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden">
                <template x-for="product in results" :key="product.id">
                    <a :href="product.url"
                       class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors">
                        <img :src="product.image || '/placeholder.png'"
                             :alt="product.name"
                             class="w-12 h-12 object-cover rounded">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 truncate" x-text="product.name"></div>
                            <div class="text-xs text-gray-500" x-text="product.price"></div>
                        </div>
                    </a>
                </template>
            </div>
        </form>

        <!-- Categories Menu -->
        <nav class="hidden md:block border-t border-gray-50 bg-white">
            <ul class="max-w-7xl mx-auto px-6 flex items-center gap-8 text-sm font-medium text-gray-500 overflow-x-auto no-scrollbar">
                <li><a href="{{ route('home') }}" class="block py-3 hover:text-brand-500 border-b-2 border-transparent hover:border-brand-500 transition-all whitespace-nowrap {{ Request::is('/') ? 'text-brand-500' : '' }}">Главная</a></li>
                <li><a href="{{ route('products.index') }}" class="block py-3 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-200 transition-all whitespace-nowrap">Все товары</a></li>
                @isset($categories)
                    @foreach($categories as $category)
                        <li><a href="{{ route('category.show', $category) }}" class="block py-3 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-200 transition-all whitespace-nowrap">{{ $category->name }}</a></li>
                    @endforeach
                @endisset
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 pt-16 pb-8 mt-20">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8 mb-12">
                <div class="col-span-2 lg:col-span-2 space-y-4 pr-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 mb-4">
                        <div class="w-6 h-6 bg-brand-500 rounded flex items-center justify-center text-white text-xs font-bold">mi</div>
                        <span class="text-lg font-semibold text-gray-900 tracking-tight">Mi Tech</span>
                    </a>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        {{ \App\Models\Setting::get('site.footer_text', 'Премиальный магазин электроники Xiaomi. Мы предоставляем лучшие устройства с официальной гарантией и превосходным сервисом.') }}
                    </p>
                    <div class="flex gap-4 pt-2">
                        <a href="#" class="text-gray-400 hover:text-brand-500 transition-colors"><iconify-icon icon="logos:instagram-icon" width="20"></iconify-icon></a>
                        <a href="#" class="text-gray-400 hover:text-brand-500 transition-colors"><iconify-icon icon="logos:facebook" width="20"></iconify-icon></a>
                        <a href="#" class="text-gray-400 hover:text-brand-500 transition-colors"><iconify-icon icon="logos:youtube-icon" width="20"></iconify-icon></a>
                    </div>
                </div>

                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900 text-sm">Каталог</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="{{ route('products.index') }}" class="hover:text-brand-500 transition-colors">Все товары</a></li>
                    </ul>
                </div>

                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900 text-sm">Клиентам</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="#" class="hover:text-brand-500 transition-colors">Связаться с нами</a></li>
                        <li><a href="#" class="hover:text-brand-500 transition-colors">Гарантия</a></li>
                        <li><a href="#" class="hover:text-brand-500 transition-colors">Доставка</a></li>
                    </ul>
                </div>

                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900 text-sm">Контакты</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li class="flex items-start gap-2">
                            <iconify-icon icon="solar:map-point-linear" class="mt-0.5 shrink-0"></iconify-icon>
                            {{ \App\Models\Setting::get('site.address', 'Бишкек, пр. Манаса 101') }}
                        </li>
                        <li class="flex items-center gap-2">
                            <iconify-icon icon="solar:phone-calling-linear" class="shrink-0"></iconify-icon>
                            {{ \App\Models\Setting::get('site.phone', '+996 (555) 00-00-00') }}
                        </li>
                        <li class="flex items-center gap-2">
                            <iconify-icon icon="solar:letter-linear" class="shrink-0"></iconify-icon>
                            {{ \App\Models\Setting::get('site.email', 'sales@gadget.kg') }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Google Maps Section -->
            @php
                $mapLat = \App\Models\Setting::get('site.map_latitude', '42.8746');
                $mapLng = \App\Models\Setting::get('site.map_longitude', '74.5698');
            @endphp
            @if($mapLat && $mapLng)
            <div class="mt-12">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Наш магазин на карте</h3>
                <div class="w-full h-[400px] rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                    <div id="map" class="w-full h-full"></div>
                </div>
            </div>
            @endif

            <div class="border-t border-gray-100 pt-8 mt-12 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-gray-400">
                <p>&copy; 2024 Mi Tech KG. Все права защищены.</p>
                <div class="flex gap-4 grayscale opacity-50">
                    <iconify-icon icon="logos:visa" width="30"></iconify-icon>
                    <iconify-icon icon="logos:mastercard" width="24"></iconify-icon>
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Notifications -->
    <x-toast />

    <!-- WhatsApp Floating Button -->
    <x-whatsapp-button />

    <!-- Scripts -->
    <script>
    function searchAutocomplete() {
        return {
            query: '{{ request('q') }}',
            results: [],
            showResults: false,
            async search() {
                if (this.query.length < 2) {
                    this.results = [];
                    return;
                }

                try {
                    const response = await fetch(`/search/autocomplete?q=${encodeURIComponent(this.query)}`);
                    const data = await response.json();
                    this.results = data.results || [];
                } catch (error) {
                    console.error('Search autocomplete error:', error);
                    this.results = [];
                }
            }
        }
    }
    </script>

    @stack('scripts')

    <!-- 2GIS Maps Script -->
    @if(\App\Models\Setting::get('site.map_latitude') && \App\Models\Setting::get('site.map_longitude'))
    <script src="https://maps.api.2gis.ru/2.0/loader.js"></script>
    <script>
        DG.then(function() {
            const lat = parseFloat('{{ \App\Models\Setting::get('site.map_latitude', '42.8746') }}');
            const lng = parseFloat('{{ \App\Models\Setting::get('site.map_longitude', '74.5698') }}');

            const map = DG.map('map', {
                center: [lat, lng],
                zoom: 16
            });

            const marker = DG.marker([lat, lng]).addTo(map);

            const popupContent = `
                <div style="padding: 10px; min-width: 200px;">
                    <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 600; color: #111;">Mi Tech</h3>
                    <p style="margin: 0 0 8px 0; font-size: 13px; color: #555; line-height: 1.5;">
                        <strong>Адрес:</strong><br>
                        {{ \App\Models\Setting::get('site.address', 'Бишкек, пр. Манаса 101') }}
                    </p>
                    <p style="margin: 0 0 8px 0; font-size: 13px; color: #555;">
                        <strong>Телефон:</strong><br>
                        {{ \App\Models\Setting::get('site.phone', '+996 (555) 00-00-00') }}
                    </p>
                    <a href="https://2gis.kg/bishkek/geo/${lng},${lat}"
                       target="_blank"
                       style="display: inline-block; margin-top: 8px; padding: 6px 12px; background: #f97316; color: white; text-decoration: none; border-radius: 6px; font-size: 12px; font-weight: 500;">
                        Открыть в 2GIS
                    </a>
                </div>
            `;

            marker.bindPopup(popupContent);
            marker.openPopup();
        });
    </script>
    @endif

</body>
</html>
