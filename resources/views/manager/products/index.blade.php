@extends('layouts.app')

@section('title', 'পণ্য ব্যবস্থাপনা')

@section('content')
@php
    $routePrefix = auth()->user()->hasRole('owner') ? 'owner' : 'manager';
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">পণ্য ব্যবস্থাপনা</h1>
        <a href="{{ route($routePrefix . '.products.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            নতুন পণ্য যোগ করুন
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">পণ্য কোড</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">পণ্যের নাম</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ক্রয় মূল্য</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">বিক্রয় মূল্য</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">বর্তমান স্টক</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">কার্যক্রম</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($products as $product)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $product->sku }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $product->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">৳{{ bn_number(number_format($product->purchase_price, 2)) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">৳{{ bn_number(number_format($product->sell_price, 2)) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ bn_number($product->current_stock) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="{{ route($routePrefix . '.products.edit', $product) }}" class="text-blue-600 hover:text-blue-900 mr-3">সম্পাদনা</a>
                        <form action="{{ route($routePrefix . '.products.destroy', $product) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('আপনি কি নিশ্চিত?')">মুছুন</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
