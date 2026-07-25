<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $brands = Brand::latest()->paginate(10)->withQueryString();
        return Inertia::render('Brands/Index', [
            'brands' => $brands
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return Inertia::render('Brands/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'slug' => 'required|string|max:255|unique:brands,slug',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->slug);
        $brand->description = $request->description;
        $brand->status = $request->status ?? 1;
        $brand->save();

        return redirect()->route('brands.index')->with('success', 'Brand created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $brand = Brand::findOrFail($id);

        return Inertia::render('Brands/Edit', [
            'brand' => $brand,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($brand->id)],
            'slug' => ['required', 'string', 'max:255', Rule::unique('brands', 'slug')->ignore($brand->id)],
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $brand->name = $request->name;
        $brand->slug = Str::slug($request->slug);
        $brand->description = $request->description;
        $brand->status = $request->status ?? 1;
        $brand->save();

        return redirect()->route('brands.index')->with('success', 'Brand updated successfully');
    }

    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully');
    }
}
