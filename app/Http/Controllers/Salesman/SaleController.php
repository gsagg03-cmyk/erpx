<?php

namespace App\Http\Controllers\Salesman;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Product;
use App\Models\ProfitRealization;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $businessId = $user->business_id;
        $businessUserIds = \App\Models\User::where('business_id', $businessId)->pluck('id');
        
        // If salesman, show only their sales
        if ($user->hasRole('salesman')) {
            $sales = Sale::where('user_id', $user->id)->with('product')->latest()->paginate(15);
        } 
        // If manager or owner, show all business sales
        else {
            $sales = Sale::whereIn('user_id', $businessUserIds)->with('product')->latest()->paginate(15);
        }
        
        return view('salesman.sales.index', compact('sales'));
    }

    public function create()
    {
        $businessId = auth()->user()->business_id;
        $products = Product::where('business_id', $businessId)->where('current_stock', '>', 0)->get();
        $baseRoute = $this->resolveBaseRoute();
        return view('salesman.sales.create', compact('products', 'baseRoute'));
    }

    public function store(Request $request)
    {
        // Check if due system is enabled for this user
        if ($request->has('is_credit') && $request->is_credit && !auth()->user()->isDueSystemEnabled()) {
            return back()->withErrors(['is_credit' => 'বাকি সিস্টেম বন্ধ আছে'])->withInput();
        }

        // Base validation rules
        $rules = [
            'cart_data' => ['required', 'json'],
            'is_credit' => ['nullable'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'expected_clear_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];

        // If credit sale, customer fields are required
        if ($request->has('is_credit') && $request->is_credit) {
            $rules['customer_name'] = ['required', 'string', 'max:255'];
            $rules['customer_phone'] = ['required', 'string', 'max:20'];
        } else {
            $rules['customer_name'] = ['nullable', 'string', 'max:255'];
            $rules['customer_phone'] = ['nullable', 'string', 'max:20'];
        }

        $validated = $request->validate($rules);

        // Parse cart data
        $cartItems = json_decode($validated['cart_data'], true);
        
        if (empty($cartItems)) {
            return back()->withErrors(['cart_data' => 'অনুগ্রহ করে পণ্য যোগ করুন'])->withInput();
        }

        // Calculate grand total
        $grandTotal = 0;
        foreach ($cartItems as $item) {
            $grandTotal += $item['total'];
        }

        // Determine paid amount
        $paidAmount = isset($validated['is_credit']) && $validated['is_credit'] 
            ? ($validated['paid_amount'] ?? 0)
            : $grandTotal;
        
        if ($paidAmount > $grandTotal) {
            return back()->withErrors(['paid_amount' => 'পরিশোধিত টাকা মোট টাকার চেয়ে বেশি হতে পারে না'])->withInput();
        }

        // Generate unique voucher number for this transaction
        $voucherNumber = 'V-' . date('YmdHis') . '-' . substr(uniqid(), -4);

        try {
            \DB::beginTransaction();

            $createdSales = [];
            $totalProfit = 0;

            // Create a sale for each product in cart
            foreach ($cartItems as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Check stock
                if ($product->current_stock < $item['quantity']) {
                    \DB::rollBack();
                    return back()->withErrors(['cart_data' => "পর্যাপ্ত স্টক নেই: {$product->name}"])->withInput();
                }

                // Calculate proportional payment for this item
                $itemTotal = $item['total'];
                $itemPaidAmount = ($itemTotal / $grandTotal) * $paidAmount;

                $sale = Sale::create([
                    'product_id' => $item['product_id'],
                    'user_id' => auth()->id(),
                    'quantity' => $item['quantity'],
                    'sell_price' => $item['price'],
                    'customer_name' => $validated['customer_name'] ?? null,
                    'customer_phone' => $validated['customer_phone'] ?? null,
                    'paid_amount' => $itemPaidAmount,
                    'expected_clear_date' => $validated['expected_clear_date'] ?? null,
                    'voucher_number' => $voucherNumber,
                ]);

                $product->reduceStock($item['quantity']);
                $createdSales[] = $sale;
                $totalProfit += $sale->profit;

                // Record initial profit realization if payment was made
                if ($itemPaidAmount > 0) {
                    $profitRatio = $sale->profit / $sale->total_amount;
                    $initialProfit = $itemPaidAmount * $profitRatio;

                    ProfitRealization::create([
                        'sale_id' => $sale->id,
                        'payment_date' => now(),
                        'payment_amount' => $itemPaidAmount,
                        'profit_amount' => $initialProfit,
                        'recorded_by' => auth()->id(),
                        'notes' => 'Initial sale payment',
                    ]);
                }
            }

            \DB::commit();

            $baseRoute = $this->resolveBaseRoute();

            return redirect()->route($baseRoute . '.sales.index')
                ->with('success', 'বিক্রয় সফলভাবে তৈরি হয়েছে। ভাউচার: ' . $voucherNumber . ' (' . count($createdSales) . ' টি পণ্য)');

        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => 'বিক্রয় তৈরিতে সমস্যা হয়েছে'])->withInput();
        }
    }

    private function resolveBaseRoute(): string
    {
        $user = auth()->user();

        if ($user->hasRole('owner')) {
            return 'owner';
        }

        if ($user->hasRole('manager')) {
            return 'manager';
        }

        return 'salesman';
    }

    public function destroy(Sale $sale)
    {
        // Check if user has permission to delete this sale
        $user = auth()->user();
        
        // Only owner can delete sales, or the salesman who created it (within same business)
        if (!$user->hasRole('owner') && $sale->user_id !== $user->id) {
            return back()->withErrors(['error' => 'আপনার এই বিক্রয় মুছার অনুমতি নেই']);
        }

        // Check if sale belongs to the same business
        if ($sale->user->business_id !== $user->business_id) {
            return back()->withErrors(['error' => 'আপনার এই বিক্রয় মুছার অনুমতি নেই']);
        }

        try {
            \DB::beginTransaction();

            // Restore stock
            $product = $sale->product;
            $product->increment('current_stock', $sale->quantity);

            // Delete all profit realizations for this sale
            ProfitRealization::where('sale_id', $sale->id)->delete();

            // Delete the sale
            $sale->delete();

            \DB::commit();

            $baseRoute = $this->resolveBaseRoute();
            return redirect()->route($baseRoute . '.all-sales')
                ->with('success', 'বিক্রয় সফলভাবে বাতিল হয়েছে। স্টক পুনরায় যোগ করা হয়েছে।');

        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => 'বিক্রয় বাতিল করতে সমস্যা হয়েছে: ' . $e->getMessage()]);
        }
    }
}
