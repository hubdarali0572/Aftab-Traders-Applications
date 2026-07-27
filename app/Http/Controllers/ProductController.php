<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['category', 'brand', 'unit'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('model_number', 'like', "%{$search}%")
                        ->orWhere('manufacturer', 'like', "%{$search}%")
                        ->orWhereHas('category', fn($q) => $q->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('brand', fn($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('ProductManagement/Products/Index', [
            'products' => $products,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {

        return Inertia::render('ProductManagement/Products/Create', $this->formOptions());
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);
         $authUser = Auth::user();

        Product::create([
            ...$validated,
            'slug' => Str::slug($validated['slug']),
            'user_id' => $authUser,
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully');
    }

    public function show(Product $product)
    {
        return Inertia::render('ProductManagement/Products/Show', [
            'product' => $product->load(['category', 'brand', 'unit']),
        ]);
    }

    public function edit(string $id)
    {
        $product = Product::findOrFail($id);

        return Inertia::render('ProductManagement/Products/Edit', [
            'product' => $product,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $validated = $this->validateProduct($request, $product);

        $product->update([
            ...$validated,
            'slug' => Str::slug($validated['slug']),
        ]);

        return redirect()->route('products.index')->with('success', 'Product updated successfully');
    }

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully');
    }

    private function formOptions(): array
    {
        return [
            'categories' => ProductCategory::where('status', true)->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::where('status', true)->orderBy('name')->get(['id', 'name']),
            'units' => Unit::where('status', true)->orderBy('name')->get(['id', 'name', 'base_value']),
        ];
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $productId = $product?->id;

        return $request->validate([
            'product_category_id' => 'required|exists:product_categories,id',
            'brand_id' => 'required|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'tax' => 'nullable|numeric|min:0|max:100',
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($productId),
            ],
            'sku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'barcode' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0',
            'hsn_code' => 'nullable|string|max:255',
            'origin_country' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'minimum_stock' => 'nullable|integer|min:0',
            'maximum_stock' => 'nullable|integer|min:0|gte:minimum_stock',
            'track_stock' => 'nullable|boolean',
            'has_expiry' => 'nullable|boolean',
            'is_serialized' => 'nullable|boolean',
            'status' => 'nullable|boolean',
        ]);
    }
}
