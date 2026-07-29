<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleReturn;
use App\Models\SaleReturnDetail;
use App\Services\InventoryPostingService;
use App\Services\SaleService;
use App\Services\UnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;

class SaleReturnController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting,
        protected SaleService $saleService,
        protected UnitService $unitService
    ) {
    }

    public function index(Request $request)
    {
        $filters = $request->only('search');

        $returns = SaleReturn::query()
            ->with(['sale', 'customer', 'warehouse', 'user'])
            ->tap(fn ($q) => $this->saleService->applyReturnFilters($q, $filters))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/SaleReturns/Index', [
            'returns' => $returns,
            'summary' => $this->saleService->returnsDashboardSummary($filters),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request)
    {
        $selectedSale = null;
        if ($request->filled('sale_id')) {
            $selectedSale = $this->salesForForms()->firstWhere('id', (int) $request->sale_id);
        }

        return Inertia::render('InventoryManagement/SaleReturns/Create', [
            'sales' => $this->salesForForms(),
            'selectedSale' => $selectedSale,
            'generatedReferenceNo' => $this->saleService->generateReturnReferenceNo(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge($this->headerRules(), $this->itemRules()));

        $sale = Sale::with('details.product')
            ->where('sale_status', 'completed')
            ->findOrFail($validated['sale_id']);

        try {
            DB::transaction(function () use ($request, $validated, $sale) {
                $saleReturn = SaleReturn::create([
                    'user_id' => Auth::id(),
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'warehouse_id' => $sale->warehouse_id,
                    'reference_no' => $validated['reference_no'],
                    'return_date' => $validated['return_date'],
                    'total_quantity' => 0,
                    'total_amount' => 0,
                    'remarks' => $validated['remarks'] ?? null,
                    'status' => $request->boolean('status', true),
                ]);

                $this->syncReturnItems($saleReturn, $request->items, $sale);
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sale-returns.index')->with('success', 'Sales return recorded successfully');
    }

    public function show(string $id)
    {
        $saleReturn = SaleReturn::with([
            'sale:id,invoice_no,sale_date',
            'customer:id,customer_name,customer_code,phone',
            'warehouse:id,name',
            'user:id,name,email',
            'details.product:id,name,sku',
            'details.unit:id,name',
        ])->findOrFail($id);

        $lineDiscount = (float) $saleReturn->details->sum('discount');
        $lineTax = (float) $saleReturn->details->sum('tax');

        return Inertia::render('InventoryManagement/SaleReturns/Show', [
            'saleReturn' => $saleReturn,
            'summary' => [
                'total_items' => $saleReturn->details->count(),
                'total_quantity' => (float) $saleReturn->total_quantity,
                'total_amount' => (float) $saleReturn->total_amount,
                'line_discount' => $lineDiscount,
                'line_tax' => $lineTax,
            ],
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/SaleReturns/Edit', [
            'saleReturn' => SaleReturn::with('details.product')->findOrFail($id),
            'sales' => $this->salesForForms(null, (int) $id),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $saleReturn = SaleReturn::with('details')->findOrFail($id);
        $validated = $request->validate(array_merge(
            $this->headerRules($saleReturn->id),
            $this->itemRules()
        ));

        $sale = Sale::with('details.product')
            ->where('sale_status', 'completed')
            ->findOrFail($validated['sale_id']);

        if ($saleReturn->details->isNotEmpty() && (int) $saleReturn->sale_id !== (int) $sale->id) {
            return redirect()->back()->withInput()->with('error', 'You cannot change the invoice once return line items exist.');
        }

        $mustResync = $saleReturn->details->isNotEmpty() && (
            $saleReturn->warehouse_id != $sale->warehouse_id
            || $saleReturn->customer_id != $sale->customer_id
            || $saleReturn->return_date->format('Y-m-d') !== $validated['return_date']
            || $saleReturn->reference_no !== $validated['reference_no']
        );

        try {
            DB::transaction(function () use ($request, $saleReturn, $validated, $sale, $mustResync) {
                $saleReturn->update([
                    'sale_id' => $sale->id,
                    'customer_id' => $sale->customer_id,
                    'warehouse_id' => $sale->warehouse_id,
                    'reference_no' => $validated['reference_no'],
                    'return_date' => $validated['return_date'],
                    'remarks' => $validated['remarks'] ?? null,
                    'status' => $request->boolean('status', true),
                ]);

                $this->syncReturnItems($saleReturn->fresh(), $request->items, $sale, $saleReturn->id);

                if ($mustResync) {
                    $this->posting->syncSaleReturnStock($saleReturn->fresh(['details']));
                }
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sale-returns.index')->with('success', 'Sales return updated successfully');
    }

    public function destroy(string $id)
    {
        $saleReturn = SaleReturn::with('details')->findOrFail($id);

        try {
            DB::transaction(function () use ($saleReturn) {
                foreach ($saleReturn->details as $detail) {
                    $this->posting->reverseSaleReturnDetail($detail);
                    $detail->delete();
                }

                $saleReturn->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Sales return deleted successfully');
    }

    protected function headerRules(?int $ignoreId = null): array
    {
        $uniqueRule = 'required|string|unique:sale_returns,reference_no';
        if ($ignoreId) {
            $uniqueRule .= ',' . $ignoreId;
        }

        return [
            'reference_no' => $uniqueRule,
            'sale_id' => 'required|exists:sales,id',
            'return_date' => 'required|date',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ];
    }

    protected function itemRules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id|distinct',
            'items.*.quantity' => 'required|numeric|gt:0',
            'items.*.reason' => 'nullable|string',
            'items.*.remarks' => 'nullable|string',
        ];
    }

    protected function syncReturnItems(SaleReturn $saleReturn, array $items, Sale $sale, ?int $ignoreReturnId = null): void
    {
        foreach ($saleReturn->details as $detail) {
            $this->posting->reverseSaleReturnDetail($detail);
        }
        $saleReturn->details()->delete();

        foreach ($items as $row) {
            $saleDetail = $this->getOriginalSaleDetail($sale, (int) $row['product_id']);

            if (! $saleDetail) {
                throw new InvalidArgumentException('Selected product does not exist on the referenced sales invoice.');
            }

            $returnQty = (float) $row['quantity'];
            $this->ensureReturnQuantityAllowed($sale, (int) $row['product_id'], $returnQty, $ignoreReturnId);

            $pricing = $this->deriveReturnAmounts($saleDetail, $returnQty);
            $unitId = $this->resolveUnitId($saleDetail);

            $detail = $saleReturn->details()->create([
                'user_id' => Auth::id(),
                'product_id' => $row['product_id'],
                'unit_id' => $unitId,
                'quantity' => $returnQty,
                'unit_price' => $pricing['unit_price'],
                'discount' => $pricing['discount'],
                'tax' => $pricing['tax'],
                'line_total' => $pricing['line_total'],
                'reason' => $row['reason'] ?? null,
                'remarks' => $row['remarks'] ?? null,
            ]);

            $this->posting->postSaleReturnDetail($detail);
        }
    }

    protected function salesForForms(?int $onlySaleId = null, ?int $ignoreReturnId = null)
    {
        $query = Sale::with([
            'customer:id,customer_name',
            'warehouse:id,name',
            'details:id,sale_id,product_id,quantity,unit_price,discount,tax,selling_unit',
            'details.product:id,name,sku',
        ])
            ->select('id', 'invoice_no', 'customer_id', 'warehouse_id')
            ->where('sale_status', 'completed')
            ->orderByDesc('id');

        if ($onlySaleId) {
            $query->where('id', $onlySaleId);
        }

        return $query->get()
            ->map(function (Sale $sale) use ($ignoreReturnId) {
                $returnedByProduct = SaleReturnDetail::query()
                    ->whereHas('saleReturn', function ($q) use ($sale, $ignoreReturnId) {
                        $q->where('sale_id', $sale->id);
                        if ($ignoreReturnId) {
                            $q->where('id', '!=', $ignoreReturnId);
                        }
                    })
                    ->selectRaw('product_id, SUM(quantity) as returned_qty')
                    ->groupBy('product_id')
                    ->pluck('returned_qty', 'product_id');

                $sale->details->transform(function ($detail) use ($returnedByProduct) {
                    $sold = (float) $detail->quantity;
                    $returned = (float) ($returnedByProduct[$detail->product_id] ?? 0);
                    $detail->returnable_qty = max(0, $sold - $returned);

                    return $detail;
                });

                return $sale;
            });
    }

    protected function getOriginalSaleDetail(Sale $sale, int $productId): ?SaleDetail
    {
        $detail = $sale->details->firstWhere('product_id', $productId)
            ?? SaleDetail::query()
                ->where('sale_id', $sale->id)
                ->where('product_id', $productId)
                ->first();

        return $detail;
    }

    protected function ensureReturnQuantityAllowed(
        Sale $sale,
        int $productId,
        float $qty,
        ?int $ignoreReturnId = null
    ): void {
        $soldQty = (float) SaleDetail::where('sale_id', $sale->id)
            ->where('product_id', $productId)
            ->value('quantity');

        $returnedQty = (float) SaleReturnDetail::query()
            ->where('product_id', $productId)
            ->whereHas('saleReturn', function ($q) use ($sale, $ignoreReturnId) {
                $q->where('sale_id', $sale->id);
                if ($ignoreReturnId) {
                    $q->where('id', '!=', $ignoreReturnId);
                }
            })
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

    protected function resolveUnitId(SaleDetail $saleDetail): int
    {
        return $this->unitService->resolveForSellingUnit((string) $saleDetail->selling_unit);
    }
}
