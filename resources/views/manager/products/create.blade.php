@extends('layouts.app')

@section('title', 'Create Product')

@section('content')
@php
    $routePrefix = auth()->user()->hasRole('owner') ? 'owner' : 'manager';
@endphp
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create New Product</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route($routePrefix . '.products.store') }}">
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Product Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('name') border-red-500 @enderror" required>
                @error('name')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="sku" class="block text-gray-700 text-sm font-bold mb-2">SKU</label>
                <input type="text" name="sku" id="sku" value="{{ old('sku') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('sku') border-red-500 @enderror" required>
                @error('sku')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="purchase_price" class="block text-gray-700 text-sm font-bold mb-2">Purchase Price</label>
                <input type="number" step="0.01" name="purchase_price" id="purchase_price" value="{{ old('purchase_price') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('purchase_price') border-red-500 @enderror" required>
                @error('purchase_price')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="sell_price" class="block text-gray-700 text-sm font-bold mb-2">Sell Price</label>
                <input type="number" step="0.01" name="sell_price" id="sell_price" value="{{ old('sell_price') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('sell_price') border-red-500 @enderror" required>
                @error('sell_price')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route($routePrefix . '.products.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Create Product
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
