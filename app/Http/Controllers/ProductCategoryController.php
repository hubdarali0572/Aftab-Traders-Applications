<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = ProductCategory::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('category_type', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('ProductManagement/Categories/Index', [
            'categories' => $categories,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('ProductManagement/Categories/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:30|unique:product_categories,code',
            'name' => 'required|string|max:255|unique:product_categories,name',
            'slug' => 'required|string|max:255|unique:product_categories,slug',
            'category_type' => 'required|string|max:255',
            'image' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);
         $authUser = Auth::user();

        ProductCategory::create([
            'user_id' =>  $authUser,
            'code' => $request->code,
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
            'category_type' => $request->category_type,
            'image' => $request->image,
            'description' => $request->description,
            'status' => $request->status ?? true,
        ]);

        return redirect()->route('product-categories.index')->with('success', 'Product category created successfully');
    }

    public function edit(string $id)
    {
        $category = ProductCategory::findOrFail($id);

        return Inertia::render('ProductManagement/Categories/Edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $category = ProductCategory::findOrFail($id);

        $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('product_categories', 'code')->ignore($category->id)],
            'name' => ['required', 'string', 'max:255', Rule::unique('product_categories', 'name')->ignore($category->id)],
            'slug' => ['required', 'string', 'max:255', Rule::unique('product_categories', 'slug')->ignore($category->id)],
            'category_type' => 'required|string|max:255',
            'image' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $category->update([
            'code' => $request->code,
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
            'category_type' => $request->category_type,
            'image' => $request->image,
            'description' => $request->description,
            'status' => $request->status ?? true,
        ]);

        return redirect()->route('product-categories.index')->with('success', 'Product category updated successfully');
    }

    public function destroy(string $id)
    {
        $category = ProductCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('product-categories.index')->with('success', 'Product category deleted successfully');
    }
}
