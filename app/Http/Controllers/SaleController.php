<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\Warehouse;
use App\Services\CustomerLedgerService;
use App\Services\CustomerService;
use App\Services\InventoryPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SaleController extends Controller
{
    protected array $saleTypes = [
        'retail', 'wholesale', 'dealer', 'distributor',
        'corporate', 'contractor', 'reseller', 'walk_in',
    ];

    protected array $paymentMethods = ['cash', 'bank', 'cheque', 'card', 'online'];

    protected array $saleStatuses = ['draft', 'completed', 'cancelled'];

    public function __construct(
        protected InventoryPostingService $posting,
        protected CustomerLedgerService $ledgerService,
        protected CustomerService $customerService
    ) {
    }

    public function index(Request $request)
    {
        $sales = Sale::query()
            ->with(['customer', 'warehouse', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('customer_name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('sale_status'), fn ($q) => $q->where('sale_status', $request->sale_status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/Sales/Index', [
            'sales' => $sales,
            'filters' => $request->only('search', 'sale_status'),
            'saleStatuses' => $this->saleStatuses,
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/Sales/Create', [
            'customers' => Customer::where('status', true)->select('id', 'customer_name', 'customer_code')->orderBy('id', 'asc')->get(),
            'warehouses' => Warehouse::select('id', 'name')->get(),
            'saleTypes' => $this->saleTypes,
            'paymentMethods' => $this->paymentMethods,
            'saleStatuses' => $this->saleStatuses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_no' => 'required|string|unique:sales,invoice_no',
            'sale_date' => 'required|date',
            'sale_type' => 'required|in:' . implode(',', $this->saleTypes),
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'payment_method' => 'required|in:' . implode(',', $this->paymentMethods),
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'other_charges' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'due_amount' => 'required|numeric|min:0',
            'sale_status' => 'required|in:' . implode(',', $this->saleStatuses),
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        if ($request->filled('customer_id')) {
            $this->customerService->assertActiveForTransaction((int) $request->customer_id);
        }

        $data = $request->all();
        if (empty($data['customer_id'])) {
            $data['customer_id'] = null;
        }

        DB::transaction(function () use ($data) {
            $sale = Sale::create(array_merge($data, ['user_id' => Auth::id()]));

            if ($sale->sale_status === 'completed') {
                $this->posting->syncSaleCustomerLedger($sale);
            }
        });

        return redirect()->route('sales.index')->with('success', 'Sale recorded successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/Sales/Show', [
            'sale' => Sale::with(['customer', 'warehouse', 'user', 'details.product'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/Sales/Edit', [
            'sale' => Sale::findOrFail($id),
            'customers' => Customer::where('status', true)->select('id', 'customer_name', 'customer_code')->orderBy('id', 'asc')->get(),
            'warehouses' => Warehouse::select('id', 'name')->get(),
            'saleTypes' => $this->saleTypes,
            'paymentMethods' => $this->paymentMethods,
            'saleStatuses' => $this->saleStatuses,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $sale = Sale::findOrFail($id);

        $request->validate([
            'invoice_no' => 'required|string|unique:sales,invoice_no,' . $id,
            'sale_date' => 'required|date',
            'sale_type' => 'required|in:' . implode(',', $this->saleTypes),
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'payment_method' => 'required|in:' . implode(',', $this->paymentMethods),
            'subtotal' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'other_charges' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'due_amount' => 'required|numeric|min:0',
            'sale_status' => 'required|in:' . implode(',', $this->saleStatuses),
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $mustResyncStock = $sale->sale_status !== $request->sale_status
            || ($request->sale_status === 'completed' && (
                $sale->warehouse_id != $request->warehouse_id ||
                $sale->sale_date->format('Y-m-d') !== $request->sale_date ||
                $sale->invoice_no !== $request->invoice_no
            ));
        $mustResyncLedger = $sale->sale_status === 'completed'
            || $request->sale_status === 'completed';

        if ($request->filled('customer_id')) {
            $this->customerService->assertActiveForTransaction((int) $request->customer_id);
        }

        $data = $request->all();
        if (empty($data['customer_id'])) {
            $data['customer_id'] = null;
        }

        DB::transaction(function () use ($data, $sale, $request, $mustResyncStock, $mustResyncLedger) {
            $sale->update($data);

            if ($mustResyncStock) {
                $this->posting->syncSaleStock($sale->fresh());
            } elseif ($mustResyncLedger && $sale->sale_status === 'completed') {
                $this->posting->syncSaleCustomerLedger($sale->fresh());
            }
        });

        return redirect()->route('sales.index')->with('success', 'Sale updated successfully');
    }

    public function destroy(string $id)
    {
        $sale = Sale::with('details')->findOrFail($id);

        DB::transaction(function () use ($sale) {
            $sale->update(['sale_status' => 'cancelled']);
            $this->posting->syncSaleStock($sale->fresh(['details']));

            foreach ($sale->details as $detail) {
                $detail->delete();
            }

            $this->ledgerService->reverse('sales', (int) $sale->id);
            $sale->delete();
        });

        return redirect()->back()->with('success', 'Sale deleted successfully');
    }
}
