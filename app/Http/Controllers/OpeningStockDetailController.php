<?php

namespace App\Http\Controllers;

use App\Models\OpeningStockDetail;
use App\Models\OpeningStock;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OpeningStockDetailController extends Controller
{
    public function index(Request $request)
    {
        $details = OpeningStockDetail::query()
            ->with(['openingStock', 'product'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('product', fn($p) => $p->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('openingStock', fn($o) => $o->where('reference_no', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('InventoryManagement/OpeningStockDetails/Index', [
            'details' => $details,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/OpeningStockDetails/Create', [
            'opening_stocks' => OpeningStock::select('id', 'reference_no')->get(),
            'products' => Product::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'opening_stock_id' => [
                'required',
                Rule::unique('opening_stock_details')->where(fn($q) => $q->where('product_id', $request->product_id))
            ],
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'status' => 'boolean',
        ]);

        OpeningStockDetail::create(array_merge($request->all(), [
            'total_cost' => $request->quantity * $request->unit_cost
        ]));

        return redirect()->route('opening-stock-details.index')->with('success', 'Line item added successfully');
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/OpeningStockDetails/Edit', [
            'detail' => OpeningStockDetail::findOrFail($id),
            'opening_stocks' => OpeningStock::select('id', 'reference_no')->get(),
            'products' => Product::select('id', 'name')->get(),
        ]);
    }

    public function show(string $id)
    {
        $detail = OpeningStockDetail::with(['openingStock', 'product'])->findOrFail($id);

        return Inertia::render('InventoryManagement/OpeningStockDetails/Show', [
            'detail' => $detail,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $detail = OpeningStockDetail::findOrFail($id);

        $request->validate([
            'opening_stock_id' => [
                'required',
                Rule::unique('opening_stock_details')
                    ->where(fn($q) => $q->where('product_id', $request->product_id))
                    ->ignore($id)
            ],
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'status' => 'boolean',
        ]);

        $detail->update(array_merge($request->all(), [
            'total_cost' => $request->quantity * $request->unit_cost
        ]));

        return redirect()->route('opening-stock-details.index')->with('success', 'Line item updated');
    }

    public function destroy(string $id)
    {
        OpeningStockDetail::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Line item removed');
    }
}
