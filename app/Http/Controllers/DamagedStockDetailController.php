<?php

namespace App\Http\Controllers;

use App\Models\DamagedStock;
use App\Models\DamagedStockDetail;
use App\Models\Product;
use App\Services\InventoryPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use InvalidArgumentException;

class DamagedStockDetailController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting
    ) {
    }

    public function index(Request $request)
    {
        $details = DamagedStockDetail::query()
            ->with(['damagedStock', 'product'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('damagedStock', fn ($d) => $d->where('reference_no', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('damaged_stock_id'), function ($query) use ($request) {
                $query->where('damaged_stock_id', $request->damaged_stock_id);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('InventoryManagement/DamagedStockDetails/Index', [
            'details' => $details,
            'damaged_stocks' => DamagedStock::select('id', 'reference_no')->get(),
            'filters' => $request->only('search', 'damaged_stock_id'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/DamagedStockDetails/Create', [
            'damaged_stocks' => DamagedStock::select('id', 'reference_no')->get(),
            'products' => Product::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'damaged_stock_id' => [
                'required',
                Rule::unique('damaged_stock_details')->where(fn ($q) => $q->where('product_id', $request->product_id)),
            ],
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',
            'damage_reason' => 'required|string',
            'batch_no' => 'nullable|string',
            'serial_no' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $detail = DamagedStockDetail::create(array_merge($request->all(), [
                    'user_id' => Auth::id(),
                    'total_cost' => $request->quantity * $request->unit_cost,
                ]));

                $this->posting->postDamagedDetail($detail);
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('damaged-stock-details.index')->with('success', 'Damaged item added successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/DamagedStockDetails/Show', [
            'detail' => DamagedStockDetail::with(['damagedStock', 'product', 'user'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/DamagedStockDetails/Edit', [
            'detail' => DamagedStockDetail::findOrFail($id),
            'damaged_stocks' => DamagedStock::select('id', 'reference_no')->get(),
            'products' => Product::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $detail = DamagedStockDetail::findOrFail($id);

        $request->validate([
            'damaged_stock_id' => [
                'required',
                Rule::unique('damaged_stock_details')
                    ->where(fn ($q) => $q->where('product_id', $request->product_id))
                    ->ignore($id),
            ],
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',
            'damage_reason' => 'required|string',
            'batch_no' => 'nullable|string',
            'serial_no' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($request, $detail) {
                $detail->update(array_merge($request->all(), [
                    'total_cost' => $request->quantity * $request->unit_cost,
                ]));

                $this->posting->postDamagedDetail($detail);
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('damaged-stock-details.index')->with('success', 'Damaged item updated successfully');
    }

    public function destroy(string $id)
    {
        $detail = DamagedStockDetail::findOrFail($id);

        try {
            DB::transaction(function () use ($detail) {
                $this->posting->reverseDamagedDetail($detail);
                $detail->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Damaged item removed successfully');
    }
}
