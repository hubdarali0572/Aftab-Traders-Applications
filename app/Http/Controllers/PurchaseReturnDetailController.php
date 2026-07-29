<?php

namespace App\Http\Controllers;

use App\Models\PurchaseReturnDetail;
use App\Services\InventoryPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class PurchaseReturnDetailController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting
    ) {
    }

    public function index()
    {
        return redirect()->route('purchase-returns.index');
    }

    public function create()
    {
        return redirect()->route('purchase-returns.create');
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
        $returnId = (int) $request->purchase_return_id;

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

        return redirect()->route('purchase-returns.show', $returnId)->with('success', 'Return line item added');
    }

    public function show(string $id)
    {
        $detail = PurchaseReturnDetail::findOrFail($id);

        return redirect()->route('purchase-returns.show', $detail->purchase_return_id);
    }

    public function edit(string $id)
    {
        $detail = PurchaseReturnDetail::findOrFail($id);

        return redirect()->route('purchase-returns.edit', $detail->purchase_return_id);
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

        return redirect()->route('purchase-returns.show', $detail->purchase_return_id)->with('success', 'Return line item updated');
    }

    public function destroy(string $id)
    {
        $detail = PurchaseReturnDetail::findOrFail($id);
        $returnId = $detail->purchase_return_id;

        try {
            DB::transaction(function () use ($detail) {
                $this->posting->reversePurchaseReturnDetail($detail);
                $detail->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('purchase-returns.show', $returnId)->with('success', 'Return line item removed');
    }
}
