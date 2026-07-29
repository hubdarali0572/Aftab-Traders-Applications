<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Stock;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CustomerService;
use App\Services\InventoryPostingService;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;

class SaleController extends Controller
{
    protected array $saleTypes = [
        'retail', 'wholesale', 'dealer', 'distributor',
        'corporate', 'contractor', 'reseller', 'walk_in',
    ];

    protected array $sellingUnits = ['piece', 'carton', 'box', 'dozen', 'bundle', 'pair'];

    protected array $saleStatuses = ['draft', 'completed', 'cancelled'];

    public function __construct(
        protected InventoryPostingService $posting,
        protected CustomerService $customerService,
        protected SaleService $saleService
    ) {
    }

    public function index(Request $request)
    {
        $filters = $request->only('search', 'sale_status');

        $sales = Sale::query()
            ->with(['customer', 'warehouse', 'user'])
            ->tap(fn ($q) => $this->saleService->applySaleFilters($q, $filters))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/Sales/Index', [
            'sales' => $sales,
            'summary' => $this->saleService->salesDashboardSummary($filters),
            'filters' => $filters,
            'saleStatuses' => $this->saleStatuses,
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/Sales/Create', [
            'customers' => Customer::where('status', true)->select('id', 'customer_name', 'customer_code')->orderBy('id')->get(),
            'warehouses' => Warehouse::select('id', 'name')->orderBy('name')->get(),
            'products' => Product::select('id', 'name', 'sku', 'selling_price')->orderBy('name')->get(),
            'users' => User::select('id', 'name')->orderBy('name')->get(),
            'warehouseStocks' => Stock::select('warehouse_id', 'product_id', 'quantity')->get(),
            'sellingUnits' => $this->sellingUnits,
            'saleTypes' => $this->saleTypes,
            'saleStatuses' => $this->saleStatuses,
            'generatedInvoiceNo' => $this->saleService->generateInvoiceNo(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge($this->headerRules(), $this->itemRules()));

        if (! empty($validated['customer_id'])) {
            $this->customerService->assertActiveForTransaction((int) $validated['customer_id']);
        }

        try {
            DB::transaction(function () use ($request, $validated) {
                $sale = Sale::create(array_merge(
                    $this->pickHeaderFields($validated),
                    [
                        'user_id' => $request->input('sales_person_id') ?: Auth::id(),
                        'subtotal' => 0,
                        'grand_total' => 0,
                        'due_amount' => 0,
                        'status' => $request->boolean('status', true),
                    ]
                ));

                $this->syncSaleItems($sale, $request->items);
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sales.index')->with('success', 'Sale recorded successfully');
    }

    public function show(string $id)
    {
        $sale = Sale::with([
            'customer:id,customer_code,customer_name,customer_type,company_name,phone,email,city,address',
            'warehouse:id,name',
            'user:id,name,email',
            'details.product:id,name,sku',
            'saleReturns:id,sale_id,reference_no,return_date,total_quantity,total_amount,status',
        ])->findOrFail($id);

        $lineDiscount = (float) $sale->details->sum('discount');
        $lineTax = (float) $sale->details->sum('tax');
        $totalQty = (float) $sale->details->sum('quantity');

        return Inertia::render('InventoryManagement/Sales/Show', [
            'sale' => $sale,
            'summary' => [
                'total_items' => $sale->details->count(),
                'total_quantity' => round($totalQty, 2),
                'line_discount' => round($lineDiscount, 2),
                'line_tax' => round($lineTax, 2),
                'payment_type' => $sale->payment_method === 'cash' ? 'cash' : 'credit',
            ],
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/Sales/Edit', [
            'sale' => Sale::with('details.product')->findOrFail($id),
            'customers' => Customer::where('status', true)->select('id', 'customer_name', 'customer_code')->orderBy('id')->get(),
            'warehouses' => Warehouse::select('id', 'name')->orderBy('name')->get(),
            'products' => Product::select('id', 'name', 'sku', 'selling_price')->orderBy('name')->get(),
            'users' => User::select('id', 'name')->orderBy('name')->get(),
            'warehouseStocks' => Stock::select('warehouse_id', 'product_id', 'quantity')->get(),
            'sellingUnits' => $this->sellingUnits,
            'saleTypes' => $this->saleTypes,
            'saleStatuses' => $this->saleStatuses,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $sale = Sale::with('details')->findOrFail($id);
        $validated = $request->validate(array_merge($this->headerRules($sale->id), $this->itemRules()));

        if (! empty($validated['customer_id'])) {
            $this->customerService->assertActiveForTransaction((int) $validated['customer_id']);
        }

        $mustResyncStock = $sale->sale_status !== $validated['sale_status']
            || ($validated['sale_status'] === 'completed' && (
                $sale->warehouse_id != $validated['warehouse_id']
                || $sale->sale_date->format('Y-m-d') !== $validated['sale_date']
                || $sale->invoice_no !== $validated['invoice_no']
            ));

        try {
            DB::transaction(function () use ($request, $sale, $validated, $mustResyncStock) {
                $sale->update(array_merge(
                    $this->pickHeaderFields($validated),
                    [
                        'user_id' => $request->input('sales_person_id') ?: $sale->user_id,
                        'status' => $request->boolean('status', true),
                    ]
                ));

                $this->syncSaleItems($sale->fresh(), $request->items);

                if ($mustResyncStock) {
                    $this->posting->syncSaleStock($sale->fresh(['details']));
                }
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sales.index')->with('success', 'Sale updated successfully');
    }

    public function destroy(string $id)
    {
        $sale = Sale::with('details')->findOrFail($id);

        try {
            DB::transaction(function () use ($sale) {
                $sale->update(['sale_status' => 'cancelled']);
                $this->posting->syncSaleStock($sale->fresh(['details']));

                foreach ($sale->details as $detail) {
                    $detail->delete();
                }

                $this->posting->syncSaleCustomerLedger($sale->fresh());
                $sale->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Sale deleted successfully');
    }

    protected function headerRules(?int $ignoreId = null): array
    {
        $uniqueRule = 'required|string|unique:sales,invoice_no';
        if ($ignoreId) {
            $uniqueRule .= ',' . $ignoreId;
        }

        return [
            'invoice_no' => $uniqueRule,
            'sale_date' => 'required|date',
            'sale_type' => 'required|in:' . implode(',', $this->saleTypes),
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'payment_method' => 'required|in:cash,bank,cheque,card,online',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'sale_status' => 'required|in:' . implode(',', $this->saleStatuses),
            'remarks' => 'nullable|string',
            'sales_person_id' => 'nullable|exists:users,id',
            'status' => 'boolean',
        ];
    }

    protected function itemRules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id|distinct',
            'items.*.selling_unit' => 'required|in:' . implode(',', $this->sellingUnits),
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.remarks' => 'nullable|string',
        ];
    }

    protected function pickHeaderFields(array $data): array
    {
        return [
            'invoice_no' => $data['invoice_no'],
            'sale_date' => $data['sale_date'],
            'sale_type' => $data['sale_type'],
            'customer_id' => $data['customer_id'] ?? null,
            'warehouse_id' => $data['warehouse_id'],
            'payment_method' => $data['payment_method'],
            'discount' => $data['discount'] ?? 0,
            'tax' => $data['tax'] ?? 0,
            'other_charges' => $data['other_charges'] ?? 0,
            'paid_amount' => $data['paid_amount'] ?? 0,
            'sale_status' => $data['sale_status'],
            'remarks' => $data['remarks'] ?? null,
        ];
    }

    protected function syncSaleItems(Sale $sale, array $items): void
    {
        foreach ($sale->details as $detail) {
            $this->posting->reverseSaleDetail($detail);
        }
        $sale->details()->delete();

        foreach ($items as $row) {
            $lineTotal = $this->computeLineTotal($row);

            $trashed = SaleDetail::onlyTrashed()
                ->where('sale_id', $sale->id)
                ->where('product_id', $row['product_id'])
                ->first();

            if ($trashed) {
                $trashed->restore();
                $trashed->update([
                    'user_id' => Auth::id(),
                    'selling_unit' => $row['selling_unit'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'discount' => $row['discount'] ?? 0,
                    'tax' => $row['tax'] ?? 0,
                    'line_total' => $lineTotal,
                    'remarks' => $row['remarks'] ?? null,
                    'status' => true,
                ]);
                $detail = $trashed->fresh();
            } else {
                $detail = $sale->details()->create([
                    'user_id' => Auth::id(),
                    'product_id' => $row['product_id'],
                    'selling_unit' => $row['selling_unit'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'discount' => $row['discount'] ?? 0,
                    'tax' => $row['tax'] ?? 0,
                    'line_total' => $lineTotal,
                    'remarks' => $row['remarks'] ?? null,
                    'status' => true,
                ]);
            }

            $this->posting->postSaleDetail($detail);
        }
    }

    protected function computeLineTotal(array $row): float
    {
        return round(
            ((float) $row['quantity'] * (float) $row['unit_price'])
            - (float) ($row['discount'] ?? 0)
            + (float) ($row['tax'] ?? 0),
            2
        );
    }
}
