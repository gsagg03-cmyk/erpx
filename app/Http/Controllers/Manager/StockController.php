<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\Business;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $businessId = $this->getBusinessId();
        $products = Product::where('business_id', $businessId)->latest()->get();
        $stockEntries = StockEntry::whereHas('product', fn($q) => $q->where('business_id', $businessId))->with(['product', 'user'])->latest()->paginate(15);
        return view('manager.stock.index', compact('products', 'stockEntries'));
    }

    public function store(Request $request)
    {
        // Check if creating a new product
        if ($request->has('create_new_product')) {
            $businessId = $this->getBusinessId();
            
            $validated = $request->validate([
                'new_product_name' => ['required', 'string', 'max:255'],
                'new_product_sku' => [
                    'required', 
                    'string', 
                    'max:255',
                    \Illuminate\Validation\Rule::unique('products', 'sku')->where(function ($query) use ($businessId) {
                        return $query->where('business_id', $businessId);
                    })
                ],
                'new_product_price' => ['required', 'numeric', 'min:0'],
                'quantity' => ['required', 'integer', 'min:1'],
                'purchase_price' => ['required', 'numeric', 'min:0'],
            ]);

            // Create new product
            $product = Product::create([
                'business_id' => $businessId,
                'name' => $validated['new_product_name'],
                'sku' => $validated['new_product_sku'],
                'sell_price' => $validated['new_product_price'],
                'current_stock' => 0,
                'purchase_price' => 0,
            ]);

            // Add stock
            $product->addStock(
                $validated['quantity'],
                $validated['purchase_price'],
                auth()->id()
            );

            return redirect()->route('manager.stock.index')->with('success', 'নতুন পণ্য তৈরি এবং স্টক যোগ করা হয়েছে।');
        }

        // Existing product stock addition
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $product->addStock(
            $validated['quantity'],
            $validated['purchase_price'],
            auth()->id()
        );

        return redirect()->route('manager.stock.index')->with('success', 'স্টক সফলভাবে যোগ করা হয়েছে।');
    }

    /**
     * Resolve or provision a business id for the authenticated user so owners can add stock/products.
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
