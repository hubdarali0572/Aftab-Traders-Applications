<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSellingPrice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProductSellingPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $productSellingPrices = ProductSellingPrice::query()
            ->with('product')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('ProductManagement/ProductSellingPrices/Index', [
            'productSellingPrices' => $productSellingPrices,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('ProductManagement/ProductSellingPrices/Create', [
            'products' => Product::select('id', 'name', 'sku')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        ProductSellingPrice::create($validated);

        return redirect()
            ->route('product-selling-prices.index')
            ->with('success', 'Selling price created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $productSellingPrice = ProductSellingPrice::with('product')->findOrFail($id);

        return Inertia::render('ProductManagement/ProductSellingPrices/Show', [
            'productSellingPrice' => $productSellingPrice,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return Inertia::render('ProductManagement/ProductSellingPrices/Edit', [
            'productSellingPrice' => ProductSellingPrice::findOrFail($id),
            'products' => Product::select('id', 'name', 'sku')->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $productSellingPrice = ProductSellingPrice::findOrFail($id);

        $validated = $request->validate($this->rules($productSellingPrice->id));

        $productSellingPrice->update($validated);

        return redirect()
            ->route('product-selling-prices.index')
            ->with('success', 'Selling price updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $productSellingPrice = ProductSellingPrice::findOrFail($id);
        $productSellingPrice->delete();

        return redirect()
            ->route('product-selling-prices.index')
            ->with('success', 'Selling price deleted successfully.');
    }

    /**
     * Shared validation rules for store/update.
     */
    protected function rules(?int $ignoreId = null): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],

            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'landing_cost' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],

            'retail_price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'dealer_price' => ['nullable', 'numeric', 'min:0'],
            'distributor_price' => ['nullable', 'numeric', 'min:0'],
            'online_price' => ['nullable', 'numeric', 'min:0'],

            'minimum_selling_price' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'profit_margin' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],

            'is_default' => ['boolean'],
            'status' => ['boolean'],
        ];
    }
}