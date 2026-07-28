<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleReturnRequest;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Services\InventoryPostingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;

class SaleReturnController extends Controller
{
    public function __construct(
        protected InventoryPostingService $posting
    ) {
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $returns = SaleReturn::query()
            ->with(['sale', 'customer', 'warehouse', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('sale', fn ($sale) => $sale->where('invoice_no', 'like', "%{$search}%"))
                        ->orWhereHas('customer', fn ($customer) => $customer->where('customer_name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/SaleReturns/Index', [
            'returns' => $returns,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/SaleReturns/Create', [
            'sales' => Sale::with(['customer:id,customer_name', 'warehouse:id,name'])
                ->select('id', 'invoice_no', 'customer_id', 'warehouse_id')
                ->where('sale_status', 'completed')
                ->get(),
        ]);
    }

    public function store(SaleReturnRequest $request)
    {
        $sale = Sale::with('customer:id,customer_name', 'warehouse:id,name')
            ->where('sale_status', 'completed')
            ->findOrFail($request->sale_id);

        SaleReturn::create([
            'user_id' => Auth::id(),
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'warehouse_id' => $sale->warehouse_id,
            'reference_no' => $request->reference_no,
            'return_date' => $request->return_date,
            'total_quantity' => 0,
            'total_amount' => 0,
            'remarks' => $request->remarks,
            'status' => $request->boolean('status', true),
        ]);

        return redirect()->route('sale-returns.index')->with('success', 'Sales return created successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/SaleReturns/Show', [
            'saleReturn' => SaleReturn::with(['sale', 'customer', 'warehouse', 'user', 'details.product', 'details.unit'])
                ->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/SaleReturns/Edit', [
            'saleReturn' => SaleReturn::findOrFail($id),
            'sales' => Sale::with(['customer:id,customer_name', 'warehouse:id,name'])
                ->select('id', 'invoice_no', 'customer_id', 'warehouse_id')
                ->where('sale_status', 'completed')
                ->get(),
        ]);
    }

    public function update(SaleReturnRequest $request, string $id)
    {
        $saleReturn = SaleReturn::withCount('details')->findOrFail($id);
        $sale = Sale::where('sale_status', 'completed')->findOrFail($request->sale_id);

        if ($saleReturn->details_count > 0 && (int) $saleReturn->sale_id !== (int) $sale->id) {
            return redirect()->back()->withInput()->with('error', 'You cannot change the invoice once return line items have been added.');
        }

        $saleReturn->update([
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'warehouse_id' => $sale->warehouse_id,
            'reference_no' => $request->reference_no,
            'return_date' => $request->return_date,
            'remarks' => $request->remarks,
            'status' => $request->boolean('status', true),
        ]);

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
}
