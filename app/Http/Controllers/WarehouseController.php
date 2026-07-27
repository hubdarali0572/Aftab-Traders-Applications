<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $warehouses = Warehouse::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();


        return Inertia::render('ProductManagement/Warehouses/Index', [
            'warehouses' => $warehouses,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('ProductManagement/Warehouses/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:30|unique:warehouses,code',
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
            'status' => 'nullable|boolean',
        ]);
 
         $authUser=Auth::user();
        Warehouse::create([
            'user_id' =>$authUser->id,
            'code' => $request->code,
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'city' => $request->city,
            'address' => $request->address,
            'is_default' => $request->is_default ?? false,
            'status' => $request->status ?? true,
        ]);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        return Inertia::render('ProductManagement/Warehouses/Show', [
            'warehouse' => $warehouse,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        return Inertia::render('ProductManagement/Warehouses/Edit', [
            'warehouse' => $warehouse,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:30|unique:warehouses,code,' . $warehouse->id,
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
            'status' => 'nullable|boolean',
        ]);

        $warehouse->update([
            'code' => $request->code,
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'city' => $request->city,
            'address' => $request->address,
            'is_default' => $request->is_default ?? false,
            'status' => $request->status ?? true,
        ]);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Warehouse deleted successfully.');
    }
}