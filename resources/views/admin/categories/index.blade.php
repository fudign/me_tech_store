@extends('layouts.admin')

@section('title', 'Категории - Админ панель')
@section('page-title', 'Категории')

@section('content')
<div class="bg-white rounded-lg shadow">
    <!-- Header with Add Button -->
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-end">
        <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <iconify-icon icon="solar:add-circle-linear" width="20"></iconify-icon>
            <span class="font-medium">Добавить категорию</span>
        </a>
    </div>

    <!-- Categories Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Товаров</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($categories as $category)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $category->name }}</div>
                            @if($category->description)
                                <div class="text-sm text-gray-500 mt-1">{{ Str::limit($category->description, 60) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <code class="text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded">{{ $category->slug }}</code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $category->products_count }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($category->is_active)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                    Активна
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                    Неактивна
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors">
                                    <iconify-icon icon="solar:pen-linear" width="16"></iconify-icon>
                                    <span>Редактировать</span>
                                </a>
                                <button
                                    type="button"
                                    onclick="showDeleteModal('{{ $category->name }}', '{{ route('admin.categories.destroy', $category) }}')"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors"
                                >
                                    <iconify-icon icon="solar:trash-bin-trash-linear" width="16"></iconify-icon>
                                    <span>Удалить</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <iconify-icon icon="solar:folder-linear" width="48" class="text-gray-300"></iconify-icon>
                                <div>
                                    <p class="text-gray-500 font-medium">Категорий пока нет</p>
                                    <p class="text-sm text-gray-400 mt-1">Создайте первую категорию для товаров</p>
                                </div>
                                <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors mt-2">
                                    <iconify-icon icon="solar:add-circle-linear" width="20"></iconify-icon>
                                    <span class="font-medium">Добавить категорию</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($categories->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $categories->links() }}
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <iconify-icon icon="solar:trash-bin-trash-linear" width="24" class="text-red-600"></iconify-icon>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Удалить категорию?</h3>
                    <p class="text-sm text-gray-500 mt-1">Вы уверены, что хотите удалить категорию <span id="deleteCategoryName" class="font-medium text-gray-900"></span>?</p>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex items-center justify-end gap-3">
            <button type="button" onclick="hideDeleteModal()" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Отмена
            </button>
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Удалить
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function showDeleteModal(categoryName, deleteUrl) {
        document.getElementById('deleteCategoryName').textContent = categoryName;
        document.getElementById('deleteForm').action = deleteUrl;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function hideDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideDeleteModal();
        }
    });

    // Close modal on background click
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideDeleteModal();
        }
    });
</script>
@endsection
