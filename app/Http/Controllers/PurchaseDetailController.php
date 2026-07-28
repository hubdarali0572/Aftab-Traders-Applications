<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Services\InventoryPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use InvalidArgumentException;

class PurchaseDetailController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting
    ) {
    }

    public function index(Request $request)
    {
        $details = PurchaseDetail::query()
            ->with(['purchase', 'product'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('purchase', fn ($p) => $p->where('purchase_no', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('purchase_id'), function ($query) use ($request) {
                $query->where('purchase_id', $request->purchase_id);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('InventoryManagement/PurchaseDetails/Index', [
            'details' => $details,
            'purchases' => Purchase::select('id', 'purchase_no')->get(),
            'filters' => $request->only('search', 'purchase_id'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/PurchaseDetails/Create', [
            'purchases' => Purchase::select('id', 'purchase_no')->get(),
            'products' => Product::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_id' => [
                'required',
                'exists:purchases,id',
                Rule::unique('purchase_details')->where(fn ($q) => $q->where('product_id', $request->product_id)),
            ],
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'free_quantity' => 'nullable|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'batch_no' => 'nullable|string',
            'serial_no' => 'nullable|string',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $lineTotal = ($request->quantity * $request->unit_price)
            - (float) ($request->discount ?? 0)
            + (float) ($request->tax ?? 0);

        try {
            DB::transaction(function () use ($request, $lineTotal) {
                $detail = PurchaseDetail::create(array_merge($request->all(), [
                    'user_id' => Auth::id(),
                    'free_quantity' => $request->free_quantity ?? 0,
                    'line_total' => $lineTotal,
                ]));

                $this->posting->postPurchaseDetail($detail);
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('purchase-details.index')->with('success', 'Purchase line item added');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/PurchaseDetails/Show', [
            'detail' => PurchaseDetail::with(['purchase', 'product', 'user'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/PurchaseDetails/Edit', [
            'detail' => PurchaseDetail::findOrFail($id),
            'purchases' => Purchase::select('id', 'purchase_no')->get(),
            'products' => Product::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $detail = PurchaseDetail::findOrFail($id);

        $request->validate([
            'purchase_id' => [
                'required',
                'exists:purchases,id',
                Rule::unique('purchase_details')
                    ->where(fn ($q) => $q->where('product_id', $request->product_id))
                    ->ignore($id),
            ],
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'free_quantity' => 'nullable|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'batch_no' => 'nullable|string',
            'serial_no' => 'nullable|string',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $lineTotal = ($request->quantity * $request->unit_price)
            - (float) ($request->discount ?? 0)
            + (float) ($request->tax ?? 0);

        try {
            DB::transaction(function () use ($request, $detail, $lineTotal) {
                $detail->update(array_merge($request->all(), [
                    'free_quantity' => $request->free_quantity ?? 0,
                    'line_total' => $lineTotal,
                ]));

                $this->posting->postPurchaseDetail($detail);
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('purchase-details.index')->with('success', 'Purchase line item updated');
    }

    public function destroy(string $id)
    {
        $detail = PurchaseDetail::findOrFail($id);

        try {
            DB::transaction(function () use ($detail) {
                $this->posting->reversePurchaseDetail($detail);
                $detail->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Purchase line item removed');
    }
}
