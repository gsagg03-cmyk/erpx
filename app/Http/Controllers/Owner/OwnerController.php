<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Sale;
use App\Models\Product;
use App\Models\ProfitRealization;
use App\Models\Expense;
use App\Models\Business;
use Carbon\Carbon;

class OwnerController extends Controller
{
    public function dashboard()
    {
        $businessId = $this->getBusinessId();
        
        // Get all users in this business
        $businessUserIds = User::where('business_id', $businessId)->pluck('id');
        
        $totalManagers = User::role('manager')->where('business_id', $businessId)->count();
        $totalSalesmen = User::role('salesman')->where('business_id', $businessId)->count();
        
        // Today's data - filtered by business users
        $todaySales = Sale::whereIn('user_id', $businessUserIds)
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');
        $todayProfit = Sale::whereIn('user_id', $businessUserIds)
            ->whereDate('created_at', Carbon::today())
            ->sum('profit');
        $todayPaid = Sale::whereIn('user_id', $businessUserIds)
            ->whereDate('created_at', Carbon::today())
            ->sum('paid_amount');
        $todayDue = Sale::whereIn('user_id', $businessUserIds)
            ->whereDate('created_at', Carbon::today())
            ->sum('due_amount');
        $todayExpenses = Expense::whereIn('user_id', $businessUserIds)
            ->whereDate('expense_date', Carbon::today())
            ->sum('amount');
        
        // This month's data - filtered by business users
        $monthSales = Sale::whereIn('user_id', $businessUserIds)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('total_amount');
        $monthProfit = Sale::whereIn('user_id', $businessUserIds)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('profit');
        $monthPaid = Sale::whereIn('user_id', $businessUserIds)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('paid_amount');
        $monthDue = Sale::whereIn('user_id', $businessUserIds)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('due_amount');
        $monthExpenses = Expense::whereIn('user_id', $businessUserIds)
            ->whereYear('expense_date', Carbon::now()->year)
            ->whereMonth('expense_date', Carbon::now()->month)
            ->sum('amount');
        
        // Calculate cash in hand (profit - expenses)
        $todayCashInHand = $todayProfit - $todayExpenses;
        $monthCashInHand = $monthProfit - $monthExpenses;
        
        // Stock value - all products (filtered by business)
        $totalStockValue = Product::where('business_id', $businessId)->get()->sum(function($product) {
            return $product->current_stock * $product->purchase_price;
        });
        
        // Customer dues - filtered by business users
        $totalDue = Sale::whereIn('user_id', $businessUserIds)
            ->where('due_amount', '>', 0)
            ->sum('due_amount');
        $dueCustomers = Sale::whereIn('user_id', $businessUserIds)
            ->where('due_amount', '>', 0)
            ->with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Recent sales - filtered by business users
        $recentSales = Sale::whereIn('user_id', $businessUserIds)
            ->with(['product', 'user'])
            ->latest()
            ->take(10)
            ->get();

        return view('owner.dashboard', compact(
            'totalManagers', 
            'totalSalesmen', 
            'todaySales', 
            'todayProfit',
            'todayPaid',
            'todayDue',
            'todayExpenses',
            'todayCashInHand',
            'monthSales',
            'monthProfit',
            'monthPaid',
            'monthDue',
            'monthExpenses',
            'monthCashInHand',
            'totalStockValue',
            'totalDue',
            'dueCustomers',
            'recentSales'
        ));
    }
    
    public function dueCustomers()
    {
        $businessId = $this->getBusinessId();
        $businessUserIds = User::where('business_id', $businessId)->pluck('id');
        
        $query = Sale::whereIn('user_id', $businessUserIds)
            ->where('due_amount', '>', 0)
            ->with(['product', 'user', 'profitRealizations']);
        
        // Search by phone or voucher
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('customer_phone', 'LIKE', '%' . $search . '%')
                  ->orWhere('voucher_number', 'LIKE', '%' . $search . '%')
                  ->orWhere('customer_name', 'LIKE', '%' . $search . '%');
            });
        }
        
        $dueCustomers = $query->orderBy('created_at', 'desc')->get();
        $totalDue = $query->sum('due_amount');
        
        return view('owner.due-customers', compact('dueCustomers', 'totalDue'));
    }
    
    public function recordPayment($saleId)
    {
        $sale = Sale::findOrFail($saleId);
        
        return view('owner.record-payment', compact('sale'));
    }
    
    public function storePayment($saleId)
    {
        $sale = Sale::findOrFail($saleId);
        
        $validated = request()->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:' . $sale->due_amount,
        ]);
        
        // Generate payment voucher number
        $date = now()->format('Ymd');
        $lastPaymentVoucher = ProfitRealization::whereDate('created_at', today())
            ->whereNotNull('payment_voucher_number')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastPaymentVoucher && $lastPaymentVoucher->payment_voucher_number) {
            $lastNumber = (int) substr($lastPaymentVoucher->payment_voucher_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        $paymentVoucherNumber = 'PV-' . $date . '-' . $newNumber;
        
        // Update sale payment
        $sale->paid_amount += $validated['payment_amount'];
        $sale->save();
        
        // Calculate proportional profit for this payment
        $profitRatio = $sale->profit / $sale->total_amount;
        $profitAmount = $validated['payment_amount'] * $profitRatio;
        
        // Record profit realization
        $profitRealization = ProfitRealization::create([
            'sale_id' => $sale->id,
            'payment_amount' => $validated['payment_amount'],
            'payment_voucher_number' => $paymentVoucherNumber,
            'profit_amount' => $profitAmount,
            'payment_date' => now(),
            'recorded_by' => auth()->id(),
        ]);
        
        return redirect()->route('owner.payment.voucher', $profitRealization->id)
            ->with('success', 'পেমেন্ট সফলভাবে রেকর্ড করা হয়েছে!');
    }
    
    public function paymentVoucher($profitRealizationId)
    {
        $profitRealization = ProfitRealization::with(['sale.product', 'sale.user'])->findOrFail($profitRealizationId);
        $sale = $profitRealization->sale;
        
        // Get owner
        $salesman = $sale->user;
        $manager = $salesman && $salesman->hasRole('manager') ? $salesman : ($salesman->creator ?? null);
        $owner = $manager && $manager->hasRole('owner') ? $manager : ($manager->creator ?? null);
        
        // Get voucher template
        $template = \App\Models\VoucherTemplate::where('owner_id', $owner->id)->first();
        
        return view('voucher.payment-voucher', compact('profitRealization', 'sale', 'template'));
    }
    
    public function allSales()
    {
        $businessId = auth()->user()->business_id;
        $businessUserIds = User::where('business_id', $businessId)->pluck('id');
        
        $query = Sale::whereIn('user_id', $businessUserIds)->with(['product', 'user']);
        
        // Date filtering
        if (request('start_date')) {
            $query->whereDate('created_at', '>=', request('start_date'));
        }
        
        if (request('end_date')) {
            $query->whereDate('created_at', '<=', request('end_date'));
        }
        
        // Voucher search
        if (request('voucher_search')) {
            $query->where('voucher_number', 'LIKE', '%' . request('voucher_search') . '%');
        }
        
        $sales = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();
        
        // Calculate totals based on filtered results
        $statsQuery = Sale::whereIn('user_id', $businessUserIds);
        if (request('start_date')) {
            $statsQuery->whereDate('created_at', '>=', request('start_date'));
        }
        if (request('end_date')) {
            $statsQuery->whereDate('created_at', '<=', request('end_date'));
        }
        if (request('voucher_search')) {
            $statsQuery->where('voucher_number', 'LIKE', '%' . request('voucher_search') . '%');
        }
        
        $totalSales = $statsQuery->sum('total_amount');
        $totalProfit = (clone $statsQuery)->sum('profit');
        $totalPaid = (clone $statsQuery)->sum('paid_amount');
        $totalDue = (clone $statsQuery)->sum('due_amount');
        
        return view('owner.all-sales', compact('sales', 'totalSales', 'totalProfit', 'totalPaid', 'totalDue'));
    }

    /**
     * Resolve or provision a business id for the authenticated user so owners can access dashboard.
     */
    private function getBusinessId(): int
    {
        $user = auth()->user();

        if ($user->business_id) {
            return $user->business_id;
        }

        $business = Business::first() ?: Business::create([
            'name' => 'Default Business',
            'owner_name' => $user->name ?? 'Owner',
            'phone' => $user->phone ?? null,
            'address' => 'N/A',
        ]);

        $user->business_id = $business->id;
        $user->save();

        return $business->id;
    }
}
