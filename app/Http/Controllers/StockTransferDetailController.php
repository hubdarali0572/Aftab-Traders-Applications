<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferDetail;
use App\Services\InventoryPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use InvalidArgumentException;

class StockTransferDetailController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting
    ) {
    }

    public function index(Request $request)
    {
        $details = StockTransferDetail::query()
            ->with(['stockTransfer', 'product'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('stockTransfer', fn ($t) => $t->where('reference_no', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('transfer_id'), function ($query) use ($request) {
                $query->where('stock_transfer_id', $request->transfer_id);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('InventoryManagement/StockTransferDetails/Index', [
            'details' => $details,
            'transfers' => StockTransfer::select('id', 'reference_no')->get(),
            'filters' => $request->only('search', 'transfer_id'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/StockTransferDetails/Create', [
            'transfers' => StockTransfer::select('id', 'reference_no')->get(),
            'products' => Product::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'stock_transfer_id' => [
                'required',
                Rule::unique('stock_transfer_details')->where(fn ($q) => $q->where('product_id', $request->product_id)),
            ],
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',
            'batch_no' => 'nullable|string',
            'serial_no' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $detail = StockTransferDetail::create(array_merge($request->all(), [
                    'user_id' => Auth::id(),
                    'total_cost' => $request->quantity * $request->unit_cost,
                ]));

                $this->posting->postTransferDetail($detail);
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('stock-transfer-details.index')->with('success', 'Transfer item added successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/StockTransferDetails/Show', [
            'detail' => StockTransferDetail::with(['stockTransfer', 'product', 'user'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/StockTransferDetails/Edit', [
            'detail' => StockTransferDetail::findOrFail($id),
            'transfers' => StockTransfer::select('id', 'reference_no')->get(),
            'products' => Product::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $detail = StockTransferDetail::findOrFail($id);

        $request->validate([
            'stock_transfer_id' => [
                'required',
                Rule::unique('stock_transfer_details')
                    ->where(fn ($q) => $q->where('product_id', $request->product_id))
                    ->ignore($id),
            ],
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',
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

                $this->posting->postTransferDetail($detail);
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('stock-transfer-details.index')->with('success', 'Transfer item updated successfully');
    }

    public function destroy(string $id)
    {
        $detail = StockTransferDetail::findOrFail($id);

        try {
            DB::transaction(function () use ($detail) {
                $this->posting->reverseTransferDetail($detail);
                $detail->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Transfer item removed successfully');
    }
}
