@extends('layouts.admin')

@section('title', 'Товары')

@section('content')
<div class="flex justify-end items-center mb-6">
    <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-5 py-2.5 rounded-lg transition-colors">
        <iconify-icon icon="solar:add-circle-linear" width="20"></iconify-icon>
        Добавить товар
    </a>
</div>

<!-- Success Message Banner -->
@if(session('success'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 3000)"
         class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center gap-2">
        <iconify-icon icon="solar:check-circle-linear" width="20"></iconify-icon>
        <span>{{ session('success') }}</span>
    </div>
@endif

<!-- Delete Confirmation Modal -->
<div x-data="{ showModal: false, deleteUrl: '', productName: '' }">
    <!-- Desktop Table -->
    <div class="hidden md:block bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Фото
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Название
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Цена
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Категории
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Статус
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Действия
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($product->main_image)
                                @php
                                    $imageSrc = filter_var($product->main_image, FILTER_VALIDATE_URL)
                                        ? $product->main_image
                                        : asset('storage/' . $product->main_image);
                                @endphp
                                <img src="{{ $imageSrc }}"
                                     alt="{{ $product->name }}"
                                     class="w-12 h-12 object-cover rounded">
                            @else
                                <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                    <iconify-icon icon="solar:image-linear" width="24" class="text-gray-400"></iconify-icon>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                            <div class="text-xs text-gray-500">{{ $product->slug }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ number_format($product->price / 100, 0, ',', ' ') }} сом</div>
                            @if($product->old_price)
                                <div class="text-xs text-gray-500 line-through">{{ number_format($product->old_price / 100, 0, ',', ' ') }} сом</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-700">
                                {{ $product->categories->pluck('name')->join(', ') ?: 'Без категории' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($product->is_active)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Активен
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Черновик
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.products.edit', $product) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 hover:text-blue-800 rounded-lg transition-colors">
                                    <iconify-icon icon="solar:pen-linear" width="16"></iconify-icon>
                                    <span class="text-xs font-medium">Редактировать</span>
                                </a>
                                <button @click="showModal = true; deleteUrl = '{{ route('admin.products.destroy', $product) }}'; productName = '{{ $product->name }}'"
                                        type="button"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 hover:text-red-800 rounded-lg transition-colors">
                                    <iconify-icon icon="solar:trash-bin-trash-linear" width="16"></iconify-icon>
                                    <span class="text-xs font-medium">Удалить</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <iconify-icon icon="solar:box-linear" width="48" class="mx-auto mb-2 text-gray-300"></iconify-icon>
                            <p>Товары не найдены</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden space-y-4">
        @forelse($products as $product)
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="flex gap-4">
                    @if($product->main_image)
                        @php
                            $imageSrc = filter_var($product->main_image, FILTER_VALIDATE_URL)
                                ? $product->main_image
                                : asset('storage/' . $product->main_image);
                        @endphp
                        <img src="{{ $imageSrc }}"
                             alt="{{ $product->name }}"
                             class="w-20 h-20 object-cover rounded">
                    @else
                        <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center">
                            <iconify-icon icon="solar:image-linear" width="32" class="text-gray-400"></iconify-icon>
                        </div>
                    @endif

                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">{{ $product->name }}</h3>
                        <p class="text-xs text-gray-500 mb-2">{{ $product->slug }}</p>

                        <div class="text-sm text-gray-900 font-medium mb-1">
                            {{ number_format($product->price / 100, 0, ',', ' ') }} сом
                            @if($product->old_price)
                                <span class="text-xs text-gray-500 line-through ml-2">{{ number_format($product->old_price / 100, 0, ',', ' ') }} сом</span>
                            @endif
                        </div>

                        <div class="text-xs text-gray-600 mb-2">
                            {{ $product->categories->pluck('name')->join(', ') ?: 'Без категории' }}
                        </div>

                        @if($product->is_active)
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Активен
                            </span>
                        @else
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                Черновик
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex gap-2 mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.products.edit', $product) }}"
                       class="flex-1 inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        <iconify-icon icon="solar:pen-linear" width="18"></iconify-icon>
                        Редактировать
                    </a>
                    <button @click="showModal = true; deleteUrl = '{{ route('admin.products.destroy', $product) }}'; productName = '{{ $product->name }}'"
                            type="button"
                            class="flex-1 inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        <iconify-icon icon="solar:trash-bin-trash-linear" width="18"></iconify-icon>
                        Удалить
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-500">
                <iconify-icon icon="solar:box-linear" width="48" class="mx-auto mb-2 text-gray-300"></iconify-icon>
                <p>Товары не найдены</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $products->links() }}
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title"
         role="dialog"
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                 @click="showModal = false"
                 aria-hidden="true"></div>

            <!-- Center modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div x-show="showModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <iconify-icon icon="solar:danger-triangle-linear" width="24" class="text-red-600"></iconify-icon>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Удалить товар?
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Вы уверены, что хотите удалить товар "<span x-text="productName"></span>"? Это действие нельзя отменить.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-2">
                    <form :action="deleteUrl" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Удалить
                        </button>
                    </form>
                    <button type="button"
                            @click="showModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Отмена
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection
