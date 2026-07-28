<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleReturnDetailRequest;
use App\Models\Product;
use App\Models\SaleDetail;
use App\Models\SaleReturn;
use App\Models\SaleReturnDetail;
use App\Models\Unit;
use App\Services\InventoryPostingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;

class SaleReturnDetailController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting
    ) {
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $details = SaleReturnDetail::query()
            ->with(['saleReturn.sale', 'product', 'unit'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('product', fn ($product) => $product->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('saleReturn', fn ($saleReturn) => $saleReturn->where('reference_no', 'like', "%{$search}%"))
                        ->orWhereHas('saleReturn.sale', fn ($sale) => $sale->where('invoice_no', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('sale_return_id'), fn ($q) => $q->where('sale_return_id', $request->sale_return_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('InventoryManagement/SaleReturnDetails/Index', [
            'details' => $details,
            'saleReturns' => SaleReturn::with('sale:id,invoice_no')
                ->select('id', 'sale_id', 'reference_no')
                ->get(),
            'filters' => $request->only('search', 'sale_return_id'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/SaleReturnDetails/Create', [
            'saleReturns' => $this->saleReturnsForForms(),
            'products' => Product::with('unit:id,name')->select('id', 'name', 'unit_id')->get(),
            'units' => Unit::select('id', 'name')->get(),
        ]);
    }

    public function store(SaleReturnDetailRequest $request)
    {
        $saleReturn = SaleReturn::with('sale.details')->findOrFail($request->sale_return_id);
        $saleDetail = $this->getOriginalSaleDetail($saleReturn, (int) $request->product_id);

        if (! $saleDetail) {
            return redirect()->back()->withInput()->with('error', 'Selected product does not exist on the referenced sales invoice.');
        }

        $returnQty = (float) $request->quantity;
        $this->ensureReturnQuantityAllowed($saleReturn, (int) $request->product_id, $returnQty);

        $pricing = $this->deriveReturnAmounts($saleDetail, $returnQty);

        try {
            DB::transaction(function () use ($request, $saleReturn, $pricing) {
                $detail = SaleReturnDetail::create([
                    'user_id' => Auth::id(),
                    'sale_return_id' => $saleReturn->id,
                    'product_id' => $request->product_id,
                    'unit_id' => $request->unit_id,
                    'quantity' => $request->quantity,
                    'unit_price' => $pricing['unit_price'],
                    'discount' => $pricing['discount'],
                    'tax' => $pricing['tax'],
                    'line_total' => $pricing['line_total'],
                    'reason' => $request->reason,
                    'remarks' => $request->remarks,
                ]);

                $this->posting->postSaleReturnDetail($detail);
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sale-return-details.index')->with('success', 'Sales return line added successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/SaleReturnDetails/Show', [
            'detail' => SaleReturnDetail::with(['saleReturn.sale', 'product', 'unit', 'user'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/SaleReturnDetails/Edit', [
            'detail' => SaleReturnDetail::findOrFail($id),
            'saleReturns' => $this->saleReturnsForForms(),
            'products' => Product::with('unit:id,name')->select('id', 'name', 'unit_id')->get(),
            'units' => Unit::select('id', 'name')->get(),
        ]);
    }

    public function update(SaleReturnDetailRequest $request, string $id)
    {
        $detail = SaleReturnDetail::findOrFail($id);
        $saleReturn = SaleReturn::with('sale.details')->findOrFail($request->sale_return_id);
        $saleDetail = $this->getOriginalSaleDetail($saleReturn, (int) $request->product_id);

        if (! $saleDetail) {
            return redirect()->back()->withInput()->with('error', 'Selected product does not exist on the referenced sales invoice.');
        }

        $returnQty = (float) $request->quantity;
        $this->ensureReturnQuantityAllowed($saleReturn, (int) $request->product_id, $returnQty, $detail->id);

        $pricing = $this->deriveReturnAmounts($saleDetail, $returnQty);

        try {
            DB::transaction(function () use ($request, $detail, $saleReturn, $pricing) {
                $detail->update([
                    'sale_return_id' => $saleReturn->id,
                    'product_id' => $request->product_id,
                    'unit_id' => $request->unit_id,
                    'quantity' => $request->quantity,
                    'unit_price' => $pricing['unit_price'],
                    'discount' => $pricing['discount'],
                    'tax' => $pricing['tax'],
                    'line_total' => $pricing['line_total'],
                    'reason' => $request->reason,
                    'remarks' => $request->remarks,
                ]);

                $this->posting->postSaleReturnDetail($detail);
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sale-return-details.index')->with('success', 'Sales return line updated successfully');
    }

    public function destroy(string $id)
    {
        $detail = SaleReturnDetail::findOrFail($id);

        try {
            DB::transaction(function () use ($detail) {
                $this->posting->reverseSaleReturnDetail($detail);
                $detail->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Sales return line removed successfully');
    }

    protected function saleReturnsForForms()
    {
        return SaleReturn::with([
            'sale:id,invoice_no',
            'sale.details:id,sale_id,product_id,quantity,unit_price,discount,tax',
        ])
            ->select('id', 'sale_id', 'reference_no')
            ->get();
    }

    protected function getOriginalSaleDetail(SaleReturn $saleReturn, int $productId): ?SaleDetail
    {
        return $saleReturn->sale?->details?->firstWhere('product_id', $productId)
            ?? SaleDetail::where('sale_id', $saleReturn->sale_id)->where('product_id', $productId)->first();
    }

    protected function ensureReturnQuantityAllowed(SaleReturn $saleReturn, int $productId, float $qty, ?int $ignoreDetailId = null): void
    {
        $soldQty = (float) SaleDetail::where('sale_id', $saleReturn->sale_id)
            ->where('product_id', $productId)
            ->value('quantity');

        $returnedQty = (float) SaleReturnDetail::query()
            ->whereHas('saleReturn', fn ($q) => $q->where('sale_id', $saleReturn->sale_id))
            ->where('product_id', $productId)
            ->when($ignoreDetailId, fn ($q) => $q->where('id', '!=', $ignoreDetailId))
            ->sum('quantity');

        if ($qty + $returnedQty > $soldQty) {
            throw new InvalidArgumentException(
                'Return quantity exceeds the remaining sale quantity. Sold: ' . number_format($soldQty, 2) .
                ', already returned: ' . number_format($returnedQty, 2) . '.'
            );
        }
    }

    protected function deriveReturnAmounts(SaleDetail $saleDetail, float $qty): array
    {
        $baseQty = max((float) $saleDetail->quantity, 0.01);
        $unitDiscount = (float) $saleDetail->discount / $baseQty;
        $unitTax = (float) $saleDetail->tax / $baseQty;
        $unitPrice = (float) $saleDetail->unit_price;
        $discount = round($unitDiscount * $qty, 2);
        $tax = round($unitTax * $qty, 2);
        $lineTotal = round(($qty * $unitPrice) - $discount + $tax, 2);

        return [
            'unit_price' => $unitPrice,
            'discount' => $discount,
            'tax' => $tax,
            'line_total' => $lineTotal,
        ];
    }
}
