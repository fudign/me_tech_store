@props(['memoryOptions' => [], 'colorOptions' => []])

<aside class="bg-white p-6 rounded-lg shadow">
    <h3 class="font-bold text-lg mb-4">Фильтры</h3>

    <form method="GET" action="{{ url()->current() }}">
        <!-- Price Range Filter -->
        <div class="mb-6">
            <h4 class="font-semibold mb-2">Цена (сом)</h4>
            <div class="grid grid-cols-2 gap-2">
                <input type="number"
                       name="price_min"
                       value="{{ request('price_min') }}"
                       placeholder="От"
                       class="px-3 py-2 border rounded"
                       min="0">
                <input type="number"
                       name="price_max"
                       value="{{ request('price_max') }}"
                       placeholder="До"
                       class="px-3 py-2 border rounded"
                       min="0">
            </div>
        </div>

        <!-- Memory Filter -->
        @if($memoryOptions->isNotEmpty())
        <div class="mb-6">
            <h4 class="font-semibold mb-2">Память</h4>
            <select name="memory" class="w-full px-3 py-2 border rounded">
                <option value="">Все</option>
                @foreach($memoryOptions as $option)
                    <option value="{{ $option }}"
                            {{ request('memory') == $option ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <!-- Color Filter -->
        @if($colorOptions->isNotEmpty())
        <div class="mb-6">
            <h4 class="font-semibold mb-2">Цвет</h4>
            <select name="color" class="w-full px-3 py-2 border rounded">
                <option value="">Все</option>
                @foreach($colorOptions as $option)
                    <option value="{{ $option }}"
                            {{ request('color') == $option ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <!-- Filter Buttons -->
        <div class="flex gap-2">
            <button type="submit"
                    class="flex-1 bg-orange-600 text-white py-2 rounded hover:bg-orange-700">
                Применить
            </button>
            <a href="{{ url()->current() }}"
               class="flex-1 bg-gray-200 text-gray-700 py-2 rounded text-center hover:bg-gray-300">
                Сбросить
            </a>
        </div>
    </form>

    <!-- Active Filters Display -->
    @if(request()->anyFilled(['price_min', 'price_max', 'memory', 'color']))
    <div class="mt-4 pt-4 border-t">
        <h4 class="font-semibold text-sm mb-2">Активные фильтры:</h4>
        <div class="flex flex-wrap gap-2">
            @if(request('price_min'))
                <span class="text-xs bg-gray-100 px-2 py-1 rounded">
                    От {{ number_format(request('price_min')) }} сом
                </span>
            @endif
            @if(request('price_max'))
                <span class="text-xs bg-gray-100 px-2 py-1 rounded">
                    До {{ number_format(request('price_max')) }} сом
                </span>
            @endif
            @if(request('memory'))
                <span class="text-xs bg-gray-100 px-2 py-1 rounded">
                    {{ request('memory') }}
                </span>
            @endif
            @if(request('color'))
                <span class="text-xs bg-gray-100 px-2 py-1 rounded">
                    {{ request('color') }}
                </span>
            @endif
        </div>
    </div>
    @endif
</aside>
