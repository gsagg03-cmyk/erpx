@extends('layouts.app')

@section('title', 'মালিক ড্যাশবোর্ড')

@section('content')
<div class="min-h-screen w-full px-2 sm:px-4 lg:px-6">
    <div class="mb-4 sm:mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">মালিক ড্যাশবোর্ড</h1>
    </div>

    <!-- Today's Summary -->
    <div class="mb-3 sm:mb-4">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-700 mb-2 sm:mb-3">আজকের সারসংক্ষেপ</h2>
    </div>
    @if(auth()->user()->isDueSystemEnabled())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-4 mb-4 sm:mb-6 lg:mb-8">
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
            <div class="text-xs sm:text-sm opacity-90">আজকের বিক্রয় (মোট)</div>
            <div class="text-2xl sm:text-3xl font-bold">৳{{ bn_number(number_format($todaySales, 2)) }}</div>
            <div class="text-xs opacity-75 mt-1">বাকি সহ</div>
        </div>
        <div class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
            <div class="text-xs sm:text-sm opacity-90">আজকের নগদ প্রাপ্তি</div>
            <div class="text-2xl sm:text-3xl font-bold">৳{{ bn_number(number_format($todayPaid, 2)) }}</div>
            <div class="text-xs opacity-75 mt-1">বাকি ছাড়া</div>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
            <div class="text-xs sm:text-sm opacity-90">আজকের বাকি</div>
            <div class="text-2xl sm:text-3xl font-bold">৳{{ bn_number(number_format($todayDue, 2)) }}</div>
            <div class="text-xs opacity-75 mt-1">নতুন বাকি</div>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
            <div class="text-xs sm:text-sm opacity-90">আজকের লাভ (মোট)</div>
            <div class="text-2xl sm:text-3xl font-bold">৳{{ bn_number(number_format($todayProfit, 2)) }}</div>
            <div class="text-xs opacity-75 mt-1">খরচ ছাড়া</div>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
            <div class="text-xs sm:text-sm opacity-90">আজকের লাভ (নীট)</div>
            <div class="text-2xl sm:text-3xl font-bold">৳{{ bn_number(number_format($todayProfit - $todayExpenses, 2)) }}</div>
            <div class="text-xs opacity-75 mt-1">খরচ বাদে</div>
        </div>
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
            <div class="text-xs sm:text-sm opacity-90">আজকের খরচ</div>
            <div class="text-2xl sm:text-3xl font-bold">৳{{ bn_number(number_format($todayExpenses, 2)) }}</div>
            <div class="text-xs opacity-75 mt-1">সব খরচ</div>
        </div>
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-4 sm:p-6 text-white col-span-1 sm:col-span-2 lg:col-span-3">
            <div class="text-xs sm:text-sm opacity-90">আজকের হাতে নগদ (লাভ - খরচ)</div>
            <div class="text-2xl sm:text-3xl font-bold">৳{{ bn_number(number_format($todayCashInHand, 2)) }}</div>
            <div class="text-xs opacity-75 mt-1">প্রাপ্ত লাভ - খরচ</div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-4 sm:p-6 text-white col-span-1 sm:col-span-2 lg:col-span-3">
            <div class="text-xs sm:text-sm opacity-90">মোট স্টক মূল্য</div>
            <div class="text-2xl sm:text-3xl font-bold">৳{{ bn_number(number_format($totalStockValue, 2)) }}</div>
            <div class="text-xs opacity-75 mt-1">ক্রয়মূল্য হিসাবে</div>
        </div>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6 lg:mb-8">
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
            <div class="text-xs sm:text-sm opacity-90">আজকের বিক্রয়</div>
            <div class="text-2xl sm:text-3xl font-bold">৳{{ bn_number(number_format($todaySales, 2)) }}</div>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
            <div class="text-xs sm:text-sm opacity-90">আজকের মোট লাভ</div>
            <div class="text-2xl sm:text-3xl font-bold">৳{{ bn_number(number_format($todayProfit, 2)) }}</div>
            <div class="text-xs opacity-75 mt-1">(খরচ ছাড়া)</div>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
            <div class="text-xs sm:text-sm opacity-90">আজকের লাভ</div>
            <div class="text-2xl sm:text-3xl font-bold">৳{{ bn_number(number_format($todayProfit - $todayExpenses, 2)) }}</div>
            <div class="text-xs opacity-75 mt-1">(খরচ সহ)</div>
        </div>
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
            <div class="text-xs sm:text-sm opacity-90">আজকের খরচ</div>
            <div class="text-2xl sm:text-3xl font-bold">৳{{ bn_number(number_format($todayExpenses, 2)) }}</div>
            <div class="text-xs opacity-75 mt-1">(সব ধরনের খরচ)</div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
            <div class="text-xs sm:text-sm opacity-90">মোট স্টক মূল্য</div>
            <div class="text-2xl sm:text-3xl font-bold">৳{{ bn_number(number_format($totalStockValue, 2)) }}</div>
        </div>
    </div>
    @endif

    <!-- This Month Summary -->
    <div class="mb-3 sm:mb-4">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-700 mb-2 sm:mb-3">এই মাসের সারসংক্ষেপ</h2>
    </div>
    @if(auth()->user()->isDueSystemEnabled())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6 lg:mb-8">
        <!-- Sales Summary -->
        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 border-l-4 border-green-500">
            <div class="text-xs sm:text-sm text-gray-600 mb-2">মাসের বিক্রয়</div>
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">মোট বিক্রয় (বাকি সহ):</span>
                    <span class="text-lg font-bold text-green-600">৳{{ bn_number(number_format($monthSales, 2)) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">নগদ প্রাপ্তি:</span>
                    <span class="text-lg font-bold text-teal-600">৳{{ bn_number(number_format($monthPaid, 2)) }}</span>
                </div>
                <div class="flex justify-between items-center border-t pt-2">
                    <span class="text-xs text-gray-500">বাকি:</span>
                    <span class="text-lg font-bold text-orange-600">৳{{ bn_number(number_format($monthDue, 2)) }}</span>
                </div>
            </div>
        </div>

        <!-- Profit Summary -->
        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 border-l-4 border-blue-500">
            <div class="text-xs sm:text-sm text-gray-600 mb-2">মাসের লাভ</div>
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">মোট লাভ (খরচ ছাড়া):</span>
                    <span class="text-lg font-bold text-blue-600">৳{{ bn_number(number_format($monthProfit, 2)) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">মাসের খরচ:</span>
                    <span class="text-lg font-bold text-red-600">৳{{ bn_number(number_format($monthExpenses, 2)) }}</span>
                </div>
                <div class="flex justify-between items-center border-t pt-2">
                    <span class="text-xs text-gray-500">নীট লাভ:</span>
                    <span class="text-lg font-bold text-emerald-600">৳{{ bn_number(number_format($monthProfit - $monthExpenses, 2)) }}</span>
                </div>
            </div>
        </div>

        <!-- Expense & Cash Summary -->
        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 border-l-4 border-red-500">
            <div class="text-xs sm:text-sm text-gray-600 mb-2">মাসের হিসাব</div>
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">মোট লাভ:</span>
                    <span class="text-lg font-bold text-blue-600">৳{{ bn_number(number_format($monthProfit, 2)) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">মাসের খরচ:</span>
                    <span class="text-lg font-bold text-red-600">৳{{ bn_number(number_format($monthExpenses, 2)) }}</span>
                </div>
                <div class="flex justify-between items-center border-t pt-2">
                    <span class="text-xs text-gray-500 font-semibold">হাতে নগদ:</span>
                    <span class="text-xl font-bold text-yellow-600">৳{{ bn_number(number_format($monthCashInHand, 2)) }}</span>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6 lg:mb-8">
        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 border-l-4 border-green-500">
            <div class="text-xs sm:text-sm text-gray-600">এই মাসের বিক্রয়</div>
            <div class="text-2xl sm:text-3xl font-bold text-green-600">৳{{ bn_number(number_format($monthSales, 2)) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 border-l-4 border-blue-500">
            <div class="text-xs sm:text-sm text-gray-600">এই মাসের মোট লাভ</div>
            <div class="text-2xl sm:text-3xl font-bold text-blue-600">৳{{ bn_number(number_format($monthProfit, 2)) }}</div>
            <div class="text-xs text-gray-500 mt-1">(খরচ ছাড়া)</div>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 border-l-4 border-emerald-500">
            <div class="text-xs sm:text-sm text-gray-600">এই মাসের লাভ</div>
            <div class="text-2xl sm:text-3xl font-bold text-emerald-600">৳{{ bn_number(number_format($monthProfit - $monthExpenses, 2)) }}</div>
            <div class="text-xs text-gray-500 mt-1">(খরচ সহ)</div>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 border-l-4 border-red-500">
            <div class="text-xs sm:text-sm text-gray-600">মাসের খরচ</div>
            <div class="text-2xl sm:text-3xl font-bold text-red-600">৳{{ bn_number(number_format($monthExpenses, 2)) }}</div>
            <div class="text-xs text-gray-500 mt-1">(সব ধরনের খরচ)</div>
        </div>
    </div>
    @endif

    <!-- Overall Summary -->
    <div class="mb-3 sm:mb-4">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-700 mb-2 sm:mb-3">সাধারণ তথ্য</h2>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-{{ auth()->user()->isDueSystemEnabled() ? '4' : '2' }} gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6 lg:mb-8">
        <div class="bg-white rounded-lg shadow p-3 sm:p-6 border-l-4 border-purple-500">
            <div class="text-xs sm:text-sm text-gray-600">আমার ম্যানেজার</div>
            <div class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $totalManagers }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-3 sm:p-6 border-l-4 border-indigo-500">
            <div class="text-xs sm:text-sm text-gray-600">মোট সেলসম্যান</div>
            <div class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $totalSalesmen }}</div>
        </div>
        @if(auth()->user()->isDueSystemEnabled())
        <div class="bg-white rounded-lg shadow p-3 sm:p-6 border-l-4 border-red-500">
            <div class="text-xs sm:text-sm text-gray-600">মোট বকেয়া</div>
            <div class="text-2xl sm:text-3xl font-bold text-red-600">৳{{ bn_number(number_format($totalDue, 2)) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-3 sm:p-6 border-l-4 border-yellow-500">
            <div class="text-xs sm:text-sm text-gray-600">বকেয়া কাস্টমার</div>
            <div class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $dueCustomers->count() }}</div>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6 lg:mb-8">
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <h2 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">দ্রুত কাজ</h2>
            <div class="space-y-2">
                <a href="{{ route('owner.sales.create') }}" class="block bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-4 rounded text-center text-sm sm:text-base">
                    নতুন বিক্রয় তৈরি করুন
                </a>
                <a href="{{ route('owner.expenses.index') }}" class="block bg-orange-500 hover:bg-orange-700 text-white font-bold py-3 px-4 rounded text-center text-sm sm:text-base">
                    খরচ ব্যবস্থাপনা
                </a>
                <a href="{{ route('owner.managers.index') }}" class="block bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded text-center text-sm sm:text-base">
                    ম্যানেজার পরিচালনা
                </a>
                <a href="{{ route('owner.products.index') }}" class="block bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-4 rounded text-center text-sm sm:text-base">
                    পণ্য পরিচালনা
                </a>
                <a href="{{ route('owner.stock.index') }}" class="block bg-purple-500 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded text-center text-sm sm:text-base">
                    স্টক পরিচালনা
                </a>
                @if(auth()->user()->isDueSystemEnabled())
                <a href="{{ route('owner.due-customers') }}" class="block bg-red-500 hover:bg-red-700 text-white font-bold py-3 px-4 rounded text-center text-sm sm:text-base">
                    বকেয়া গ্রাহক দেখুন
                </a>
                @endif
                <a href="{{ route('owner.all-sales') }}" class="block bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded text-center text-sm sm:text-base">
                    সকল বিক্রয় দেখুন
                </a>
                <a href="{{ route('owner.reports') }}" class="block bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-3 px-4 rounded text-center text-sm sm:text-base">
                    রিপোর্ট দেখুন
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <h2 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">সাম্প্রতিক বিক্রয়</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs sm:text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">পণ্য</th>
                            <th class="text-left py-2">সেলসম্যান</th>
                            <th class="text-left py-2">পরিমাণ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr class="border-b">
                            <td class="py-2">{{ $sale->product->name }}</td>
                            <td class="py-2">{{ $sale->user->name }}</td>
                            <td class="py-2">৳{{ bn_number(number_format($sale->total_amount, 2)) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-4 text-center text-gray-500">কোন বিক্রয় নেই</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
