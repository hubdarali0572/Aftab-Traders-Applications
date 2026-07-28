<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Services\InventoryPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use InvalidArgumentException;

class SaleDetailController extends Controller
{
    protected array $sellingUnits = ['piece', 'carton', 'box', 'dozen', 'bundle', 'pair'];

    public function __construct(
        protected InventoryPostingService $posting
    ) {
    }

    public function index(Request $request)
    {
        $details = SaleDetail::query()
            ->with(['sale', 'product'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('sale', fn ($s) => $s->where('invoice_no', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('sale_id'), fn ($q) => $q->where('sale_id', $request->sale_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('InventoryManagement/SaleDetails/Index', [
            'details' => $details,
            'sales' => Sale::select('id', 'invoice_no')->get(),
            'filters' => $request->only('search', 'sale_id'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/SaleDetails/Create', [
            'sales' => Sale::select('id', 'invoice_no')->get(),
            'products' => Product::select('id', 'name')->get(),
            'sellingUnits' => $this->sellingUnits,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => [
                'required',
                Rule::unique('sale_details')->where(fn ($q) => $q->where('product_id', $request->product_id)),
            ],
            'product_id' => 'required|exists:products,id',
            'selling_unit' => 'required|in:' . implode(',', $this->sellingUnits),
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $lineTotal = ((float) $request->quantity * (float) $request->unit_price)
            - (float) $request->discount + (float) $request->tax;

        try {
            DB::transaction(function () use ($request, $lineTotal) {
                $detail = SaleDetail::create([
                    'user_id' => Auth::id(),
                    'sale_id' => $request->sale_id,
                    'product_id' => $request->product_id,
                    'selling_unit' => $request->selling_unit,
                    'quantity' => $request->quantity,
                    'unit_price' => $request->unit_price,
                    'discount' => $request->discount,
                    'tax' => $request->tax,
                    'line_total' => $lineTotal,
                    'remarks' => $request->remarks,
                    'status' => $request->boolean('status', true),
                ]);

                $this->posting->postSaleDetail($detail);
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sale-details.index')->with('success', 'Sale item added successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/SaleDetails/Show', [
            'detail' => SaleDetail::with(['sale.customer', 'sale.warehouse', 'product'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/SaleDetails/Edit', [
            'detail' => SaleDetail::findOrFail($id),
            'sales' => Sale::select('id', 'invoice_no')->get(),
            'products' => Product::select('id', 'name')->get(),
            'sellingUnits' => $this->sellingUnits,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $detail = SaleDetail::findOrFail($id);

        $request->validate([
            'sale_id' => [
                'required',
                Rule::unique('sale_details')
                    ->where(fn ($q) => $q->where('product_id', $request->product_id))
                    ->ignore($id),
            ],
            'product_id' => 'required|exists:products,id',
            'selling_unit' => 'required|in:' . implode(',', $this->sellingUnits),
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $lineTotal = ((float) $request->quantity * (float) $request->unit_price)
            - (float) $request->discount + (float) $request->tax;

        try {
            DB::transaction(function () use ($request, $detail, $lineTotal) {
                $detail->update([
                    'sale_id' => $request->sale_id,
                    'product_id' => $request->product_id,
                    'selling_unit' => $request->selling_unit,
                    'quantity' => $request->quantity,
                    'unit_price' => $request->unit_price,
                    'discount' => $request->discount,
                    'tax' => $request->tax,
                    'line_total' => $lineTotal,
                    'remarks' => $request->remarks,
                    'status' => $request->boolean('status', true),
                ]);

                $this->posting->postSaleDetail($detail);
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('sale-details.index')->with('success', 'Sale item updated successfully');
    }

    public function destroy(string $id)
    {
        $detail = SaleDetail::findOrFail($id);

        try {
            DB::transaction(function () use ($detail) {
                $this->posting->reverseSaleDetail($detail);
                $detail->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Sale item removed successfully');
    }
}
