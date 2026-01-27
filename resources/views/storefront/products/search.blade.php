@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-4">
        Результаты поиска: "{{ $query }}"
        <span class="text-gray-500 text-lg">({{ $total }} товаров)</span>
    </h1>

    @if($products->isEmpty())
        <div class="text-center py-12">
            <p class="text-gray-600 mb-4">По вашему запросу ничего не найдено</p>
            <a href="{{ route('products.index') }}" class="text-orange-600 hover:underline">Посмотреть все товары</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
