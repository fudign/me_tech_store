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
        <div class="max-w-7xl mx-auto px-4 md:px-6 h-10 flex items-center justify-between">
            <div class="flex items-center gap-2 md:gap-4">
                <a href="tel:{{ \App\Models\Setting::get('site.phone', '+996 (555) 00-00-00') }}" class="flex items-center gap-1.5 hover:text-gray-900 cursor-pointer transition-colors">
                    <iconify-icon icon="solar:phone-calling-linear" stroke-width="1.5"></iconify-icon>
                    <span class="hidden sm:inline">{{ \App\Models\Setting::get('site.phone', '+996 (555) 00-00-00') }}</span>
                </a>
                <span class="hidden lg:flex items-center gap-1.5 hover:text-gray-900 cursor-pointer transition-colors">
                    <iconify-icon icon="solar:map-point-linear" stroke-width="1.5"></iconify-icon>
                    {{ \App\Models\Setting::get('site.address', 'Бишкек, пр. Манаса 101') }}
                </span>
            </div>
            <div class="flex items-center gap-2 md:gap-4">
                <a href="#" class="hidden md:inline hover:text-gray-900 transition-colors">Поддержка</a>
                <a href="#" class="hidden md:inline hover:text-gray-900 transition-colors">Гарантия</a>
                <div class="hidden md:block w-px h-3 bg-gray-200"></div>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-1 hover:text-brand-500 transition-colors font-medium">
                    <iconify-icon icon="solar:settings-linear" width="14" stroke-width="2"></iconify-icon>
                    <span class="hidden lg:inline">Админ-панель</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Header / Navigation -->
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 md:px-6 h-16 md:h-20 flex items-center justify-between gap-4 md:gap-8">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 group flex-shrink-0">
                <div class="w-7 h-7 md:w-8 md:h-8 bg-brand-500 rounded-lg flex items-center justify-center text-white text-base md:text-lg font-semibold group-hover:scale-105 transition-transform duration-300">
                    mi
                </div>
                <span class="text-lg md:text-xl tracking-tight font-semibold text-gray-900">Mi Tech</span>
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
                       placeholder="Начните вводить название товара..."
                       class="w-full bg-gray-50 border border-gray-200 rounded-full py-2.5 pl-10 pr-4 text-sm outline-none focus:ring-2 focus:ring-brand-100 focus:border-brand-500 transition-all placeholder:text-gray-400"
                       maxlength="200"
                       autocomplete="off">

                <!-- Dropdown results -->
                <div x-show="showResults && (results.length > 0 || loading || (searched && results.length === 0))"
                     x-transition
                     class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden">

                    <!-- Loading state -->
                    <div x-show="loading" class="p-4 text-center text-gray-500">
                        <iconify-icon icon="svg-spinners:ring-resize" width="24" class="inline-block"></iconify-icon>
                        <span class="ml-2 text-sm">Поиск...</span>
                    </div>

                    <!-- Results -->
                    <template x-for="product in results" :key="product.id">
                        <a :href="product.url"
                           class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-0">
                            <img :src="product.image || '/placeholder.png'"
                                 :alt="product.name"
                                 class="w-12 h-12 object-cover rounded"
                                 onerror="this.src='/placeholder.png'">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900 truncate" x-text="product.name"></div>
                                <div class="text-xs text-brand-600 font-semibold" x-text="product.price"></div>
                            </div>
                            <iconify-icon icon="solar:arrow-right-linear" width="20" class="text-gray-400"></iconify-icon>
                        </a>
                    </template>

                    <!-- Empty state -->
                    <div x-show="!loading && searched && results.length === 0" class="p-6 text-center">
                        <iconify-icon icon="solar:magnifer-linear" width="48" class="text-gray-300 mb-2"></iconify-icon>
                        <p class="text-sm text-gray-500">Ничего не найдено</p>
                        <p class="text-xs text-gray-400 mt-1">Попробуйте другой запрос</p>
                    </div>
                </div>
            </form>

            <!-- Actions -->
            <div class="flex items-center gap-1 md:gap-4" x-data="{ cartCount: {{ Cart::getTotalQuantity() }}, wishlistCount: {{ count(session('wishlist', [])) }} }" @cart-updated.window="cartCount = $event.detail.count" @wishlist-updated.window="wishlistCount = $event.detail.count">
                <a href="{{ route('wishlist.index') }}" class="p-2 text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-full transition-all relative">
                    <iconify-icon icon="solar:heart-linear" width="22" stroke-width="1.5" class="md:w-6"></iconify-icon>
                    <span x-show="wishlistCount > 0"
                          x-text="wishlistCount"
                          class="absolute top-0 right-0 bg-red-500 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center font-bold"></span>
                </a>
                <a href="{{ route('cart.index') }}" class="flex items-center gap-1 md:gap-2 pl-2 pr-3 md:pr-4 py-1.5 bg-gray-900 text-white rounded-full hover:bg-gray-800 transition-all active:scale-95 group">
                    <div class="relative">
                        <iconify-icon icon="solar:cart-large-minimalistic-linear" width="18" stroke-width="1.5" class="md:w-5"></iconify-icon>
                        <span x-show="cartCount > 0" x-text="cartCount" class="absolute -top-1 -right-1 bg-brand-500 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center font-bold"></span>
                    </div>
                    <span class="text-xs md:text-sm font-medium cart-count hidden sm:inline">Корзина</span>
                </a>
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="md:hidden p-2 text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                    <iconify-icon :icon="mobileMenuOpen ? 'solar:close-circle-linear' : 'solar:hamburger-menu-linear'" width="24" stroke-width="1.5"></iconify-icon>
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
                       placeholder="Начните вводить название..."
                       class="w-full bg-gray-50 border border-gray-200 rounded-lg py-2 pl-9 text-sm focus:border-brand-500 outline-none"
                       maxlength="200"
                       autocomplete="off">
            </div>

            <!-- Dropdown results -->
            <div x-show="showResults && (results.length > 0 || loading || (searched && results.length === 0))"
                 x-transition
                 class="absolute left-6 right-6 mt-2 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden">

                <!-- Loading state -->
                <div x-show="loading" class="p-4 text-center text-gray-500">
                    <iconify-icon icon="svg-spinners:ring-resize" width="24" class="inline-block"></iconify-icon>
                    <span class="ml-2 text-sm">Поиск...</span>
                </div>

                <!-- Results -->
                <template x-for="product in results" :key="product.id">
                    <a :href="product.url"
                       class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-0">
                        <img :src="product.image || '/placeholder.png'"
                             :alt="product.name"
                             class="w-12 h-12 object-cover rounded"
                             onerror="this.src='/placeholder.png'">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 truncate" x-text="product.name"></div>
                            <div class="text-xs text-brand-600 font-semibold" x-text="product.price"></div>
                        </div>
                        <iconify-icon icon="solar:arrow-right-linear" width="20" class="text-gray-400"></iconify-icon>
                    </a>
                </template>

                <!-- Empty state -->
                <div x-show="!loading && searched && results.length === 0" class="p-6 text-center">
                    <iconify-icon icon="solar:magnifer-linear" width="48" class="text-gray-300 mb-2"></iconify-icon>
                    <p class="text-sm text-gray-500">Ничего не найдено</p>
                    <p class="text-xs text-gray-400 mt-1">Попробуйте другой запрос</p>
                </div>
            </div>
        </form>

        <!-- Categories Menu -->
        <nav class="hidden md:block border-t border-blue-800 bg-blue-900">
            <ul class="max-w-7xl mx-auto px-6 flex items-center gap-8 text-sm font-medium text-gray-200 overflow-x-auto no-scrollbar">
                <li><a href="{{ route('home') }}" class="block py-3 hover:text-white border-b-2 border-transparent hover:border-brand-500 transition-all whitespace-nowrap {{ Request::is('/') ? 'text-brand-500' : '' }}">Главная</a></li>
                <li><a href="{{ route('products.index') }}" class="block py-3 hover:text-white border-b-2 border-transparent hover:border-blue-400 transition-all whitespace-nowrap">Все товары</a></li>
                @isset($categories)
                    @foreach($categories as $category)
                        <li><a href="{{ route('category.show', $category) }}" class="block py-3 hover:text-white border-b-2 border-transparent hover:border-blue-400 transition-all whitespace-nowrap">{{ $category->name }}</a></li>
                    @endforeach
                @endisset
            </ul>
        </nav>

        <!-- Mobile Menu Drawer -->
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileMenuOpen = false"
             class="fixed inset-0 bg-black/50 z-50 md:hidden"
             style="display: none;">
        </div>

        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="fixed top-0 right-0 bottom-0 w-4/5 max-w-sm bg-white shadow-2xl z-50 overflow-y-auto md:hidden"
             style="display: none;">

            <div class="p-6">
                <!-- Mobile Menu Header -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-brand-500 rounded-lg flex items-center justify-center text-white text-lg font-semibold">
                            mi
                        </div>
                        <span class="text-xl tracking-tight font-semibold text-gray-900">Mi Tech</span>
                    </div>
                    <button @click="mobileMenuOpen = false" class="p-2 hover:bg-gray-100 rounded-lg">
                        <iconify-icon icon="solar:close-circle-linear" width="24"></iconify-icon>
                    </button>
                </div>

                <!-- Mobile Menu Links -->
                <nav class="space-y-1">
                    <a href="{{ route('home') }}" class="block px-4 py-3 rounded-lg hover:bg-gray-50 transition-colors font-medium {{ Request::is('/') ? 'bg-brand-50 text-brand-600' : 'text-gray-900' }}">
                        <iconify-icon icon="solar:home-2-linear" width="20" class="inline mr-2"></iconify-icon>
                        Главная
                    </a>
                    <a href="{{ route('products.index') }}" class="block px-4 py-3 rounded-lg hover:bg-gray-50 transition-colors font-medium text-gray-900">
                        <iconify-icon icon="solar:bag-4-linear" width="20" class="inline mr-2"></iconify-icon>
                        Все товары
                    </a>

                    <!-- Categories -->
                    @isset($categories)
                        <div class="pt-4 pb-2">
                            <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Категории</h3>
                        </div>
                        @foreach($categories as $category)
                            <a href="{{ route('category.show', $category) }}" class="block px-4 py-3 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                                <iconify-icon icon="solar:smartphone-2-linear" width="18" class="inline mr-2"></iconify-icon>
                                {{ $category->name }}
                            </a>
                        @endforeach
                    @endisset

                    <!-- Additional Links -->
                    <div class="pt-4 pb-2">
                        <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Помощь</h3>
                    </div>
                    <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                        <iconify-icon icon="solar:help-linear" width="18" class="inline mr-2"></iconify-icon>
                        Поддержка
                    </a>
                    <a href="#" class="block px-4 py-3 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                        <iconify-icon icon="solar:verified-check-linear" width="18" class="inline mr-2"></iconify-icon>
                        Гарантия
                    </a>
                </nav>

                <!-- Contact Info -->
                <div class="mt-8 p-4 bg-gray-50 rounded-xl">
                    <h3 class="font-semibold text-gray-900 mb-3 text-sm">Контакты</h3>
                    <div class="space-y-2 text-sm text-gray-600">
                        <a href="tel:{{ \App\Models\Setting::get('site.phone', '+996 (555) 00-00-00') }}" class="flex items-center gap-2 hover:text-brand-500">
                            <iconify-icon icon="solar:phone-calling-linear" width="16"></iconify-icon>
                            {{ \App\Models\Setting::get('site.phone', '+996 (555) 00-00-00') }}
                        </a>
                        <div class="flex items-start gap-2">
                            <iconify-icon icon="solar:map-point-linear" width="16" class="mt-0.5 flex-shrink-0"></iconify-icon>
                            <span>{{ \App\Models\Setting::get('site.address', 'Бишкек, пр. Манаса 101') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 pt-12 md:pt-16 pb-6 md:pb-8 mt-12 md:mt-20">
        <div class="max-w-7xl mx-auto px-4 md:px-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 md:gap-8 mb-8 md:mb-12">
                <div class="col-span-1 sm:col-span-2 lg:col-span-2 space-y-3 md:space-y-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 mb-4">
                        <div class="w-6 h-6 bg-brand-500 rounded flex items-center justify-center text-white text-xs font-bold">mi</div>
                        <span class="text-lg font-semibold text-gray-900 tracking-tight">Mi Tech</span>
                    </a>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        {{ \App\Models\Setting::get('site.footer_text', 'Премиальный магазин электроники Xiaomi. Мы предоставляем лучшие устройства с официальной гарантией и превосходным сервисом.') }}
                    </p>
                    <div class="flex gap-4 pt-2">
                        @php
                            $instagram = \App\Models\Setting::get('site.social_instagram', '');
                            $facebook = \App\Models\Setting::get('site.social_facebook', '');
                            $youtube = \App\Models\Setting::get('site.social_youtube', '');
                        @endphp
                        @if($instagram)
                            <a href="{{ $instagram }}" target="_blank" class="text-gray-400 hover:text-brand-500 transition-colors"><iconify-icon icon="logos:instagram-icon" width="20"></iconify-icon></a>
                        @endif
                        @if($facebook)
                            <a href="{{ $facebook }}" target="_blank" class="text-gray-400 hover:text-brand-500 transition-colors"><iconify-icon icon="logos:facebook" width="20"></iconify-icon></a>
                        @endif
                        @if($youtube)
                            <a href="{{ $youtube }}" target="_blank" class="text-gray-400 hover:text-brand-500 transition-colors"><iconify-icon icon="logos:youtube-icon" width="20"></iconify-icon></a>
                        @endif
                    </div>
                </div>

                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900 text-sm">{{ \App\Models\Setting::get('footer.catalog_title', 'Каталог') }}</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="{{ route('products.index') }}" class="hover:text-brand-500 transition-colors">Все товары</a></li>
                    </ul>
                </div>

                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900 text-sm">{{ \App\Models\Setting::get('footer.clients_title', 'Клиентам') }}</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        @php
                            $clientsText = \App\Models\Setting::get('footer.clients_text', "Связаться с нами\nГарантия\nДоставка");
                            $clientsLines = explode("\n", $clientsText);
                        @endphp
                        @foreach($clientsLines as $line)
                            @if(trim($line))
                                <li><a href="#" class="hover:text-brand-500 transition-colors">{{ trim($line) }}</a></li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900 text-sm">{{ \App\Models\Setting::get('footer.contacts_title', 'Контакты') }}</h4>
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
            <div class="mt-8 md:mt-12">
                <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-3 md:mb-4">Наш магазин на карте</h3>
                <div class="w-full h-[300px] md:h-[400px] rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                    <div id="map" class="w-full h-full"></div>
                </div>
            </div>
            @endif

            <div class="border-t border-gray-100 pt-6 md:pt-8 mt-8 md:mt-12 flex flex-col md:flex-row justify-between items-center gap-3 md:gap-4 text-xs text-gray-400">
                <p class="text-center md:text-left">&copy; 2024 Mi Tech KG. Все права защищены.</p>
                <div class="flex gap-3 md:gap-4 grayscale opacity-50">
                    <iconify-icon icon="logos:visa" width="28"></iconify-icon>
                    <iconify-icon icon="logos:mastercard" width="22"></iconify-icon>
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Notifications -->
    <x-toast />

    <!-- Order Success Modal -->
    <x-order-success-modal />

    <!-- WhatsApp Floating Button -->
    <x-whatsapp-button />

    <!-- Scripts -->
    <script>
    function searchAutocomplete() {
        return {
            query: '{{ request('q') }}',
            results: [],
            showResults: false,
            loading: false,
            searched: false,
            async search() {
                if (this.query.length < 1) {
                    this.results = [];
                    this.searched = false;
                    return;
                }

                this.loading = true;
                this.searched = true;

                try {
                    const response = await fetch(`/search/autocomplete?q=${encodeURIComponent(this.query)}`);
                    const data = await response.json();
                    this.results = data.results || [];
                } catch (error) {
                    console.error('Search autocomplete error:', error);
                    this.results = [];
                } finally {
                    this.loading = false;
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
