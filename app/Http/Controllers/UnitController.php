<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('short_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('ProductManagement/Units/Index', [
            'units' => $units,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('ProductManagement/Units/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'short_name' => 'required|string|max:30|unique:units,short_name',
            'base_value' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);
        $authUser = Auth::user();
        
        Unit::create([
            'user_id' =>  $authUser->id,
            'name' => $request->name,
            'short_name' => $request->short_name,
            'base_value' => $request->base_value,
            'description' => $request->description,
            'status' => $request->status ?? true,
        ]);

        return redirect()->route('units.index')->with('success', 'Unit created successfully');
    }

    public function edit(string $id)
    {
        $unit = Unit::findOrFail($id);

        return Inertia::render('ProductManagement/Units/Edit', [
            'unit' => $unit,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $unit = Unit::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('units', 'name')->ignore($unit->id)],
            'short_name' => ['required', 'string', 'max:30', Rule::unique('units', 'short_name')->ignore($unit->id)],
            'base_value' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $unit->update([
            'name' => $request->name,
            'short_name' => $request->short_name,
            'base_value' => $request->base_value,
            'description' => $request->description,
            'status' => $request->status ?? true,
        ]);

        return redirect()->route('units.index')->with('success', 'Unit updated successfully');
    }

    public function destroy(string $id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Unit deleted successfully');
    }
}
