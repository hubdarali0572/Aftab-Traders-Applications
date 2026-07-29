<?php

namespace App\Http\Controllers;

use App\Models\PurchaseDetail;
use App\Services\InventoryPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class PurchaseDetailController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting
    ) {
    }

    public function index()
    {
        return redirect()->route('purchases.index');
    }

    public function create()
    {
        return redirect()->route('purchases.create');
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

        $purchaseId = (int) $request->purchase_id;

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

        return redirect()->route('purchases.show', $purchaseId)->with('success', 'Purchase line item added');
    }

    public function show(string $id)
    {
        $detail = PurchaseDetail::findOrFail($id);

        return redirect()->route('purchases.show', $detail->purchase_id);
    }

    public function edit(string $id)
    {
        $detail = PurchaseDetail::findOrFail($id);

        return redirect()->route('purchases.edit', $detail->purchase_id);
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

        return redirect()->route('purchases.show', $detail->purchase_id)->with('success', 'Purchase line item updated');
    }

    public function destroy(string $id)
    {
        $detail = PurchaseDetail::findOrFail($id);
        $purchaseId = $detail->purchase_id;

        try {
            DB::transaction(function () use ($detail) {
                $this->posting->reversePurchaseDetail($detail);
                $detail->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('purchases.show', $purchaseId)->with('success', 'Purchase line item removed');
    }
}
