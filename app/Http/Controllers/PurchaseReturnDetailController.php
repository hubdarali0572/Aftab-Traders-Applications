<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetail;
use App\Services\InventoryPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use InvalidArgumentException;

class PurchaseReturnDetailController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting
    ) {
    }

    public function index(Request $request)
    {
        $details = PurchaseReturnDetail::query()
            ->with(['purchaseReturn', 'product'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('purchaseReturn', fn ($r) => $r->where('reference_no', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('purchase_return_id'), function ($query) use ($request) {
                $query->where('purchase_return_id', $request->purchase_return_id);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('InventoryManagement/PurchaseReturnDetails/Index', [
            'details' => $details,
            'purchaseReturns' => PurchaseReturn::select('id', 'reference_no')->get(),
            'filters' => $request->only('search', 'purchase_return_id'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/PurchaseReturnDetails/Create', [
            'purchaseReturns' => PurchaseReturn::select('id', 'reference_no')->get(),
            'products' => Product::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_return_id' => [
                'required',
                'exists:purchase_returns,id',
                Rule::unique('purchase_return_details')->where(fn ($q) => $q->where('product_id', $request->product_id)),
            ],
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'reason' => 'nullable|string',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $totalPrice = $request->quantity * $request->unit_price;

        try {
            DB::transaction(function () use ($request, $totalPrice) {
                $detail = PurchaseReturnDetail::create(array_merge($request->all(), [
                    'user_id' => Auth::id(),
                    'total_price' => $totalPrice,
                ]));

                $this->posting->postPurchaseReturnDetail($detail);
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('purchase-return-details.index')->with('success', 'Return line item added');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/PurchaseReturnDetails/Show', [
            'detail' => PurchaseReturnDetail::with(['purchaseReturn', 'product', 'user'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/PurchaseReturnDetails/Edit', [
            'detail' => PurchaseReturnDetail::findOrFail($id),
            'purchaseReturns' => PurchaseReturn::select('id', 'reference_no')->get(),
            'products' => Product::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $detail = PurchaseReturnDetail::findOrFail($id);

        $request->validate([
            'purchase_return_id' => [
                'required',
                'exists:purchase_returns,id',
                Rule::unique('purchase_return_details')
                    ->where(fn ($q) => $q->where('product_id', $request->product_id))
                    ->ignore($id),
            ],
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'reason' => 'nullable|string',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $totalPrice = $request->quantity * $request->unit_price;

        try {
            DB::transaction(function () use ($request, $detail, $totalPrice) {
                $detail->update(array_merge($request->all(), [
                    'total_price' => $totalPrice,
                ]));

                $this->posting->postPurchaseReturnDetail($detail);
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('purchase-return-details.index')->with('success', 'Return line item updated');
    }

    public function destroy(string $id)
    {
        $detail = PurchaseReturnDetail::findOrFail($id);

        try {
            DB::transaction(function () use ($detail) {
                $this->posting->reversePurchaseReturnDetail($detail);
                $detail->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Return line item removed');
    }
}
