<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderDetailRequest;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Unit;
use App\Services\OrderConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class OrderDetailController extends Controller
{
    public function __construct(
        protected OrderConversionService $conversion
    ) {
    }

    public function index(Request $request)
    {
        $details = OrderDetail::query()
            ->with(['order', 'product', 'unit'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('order', fn ($o) => $o->where('order_no', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('order_id'), fn ($q) => $q->where('order_id', $request->order_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('InventoryManagement/OrderDetails/Index', [
            'details' => $details,
            'orders' => Order::select('id', 'order_no')->latest()->get(),
            'filters' => $request->only('search', 'order_id'),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('InventoryManagement/OrderDetails/Create', [
            'orders' => Order::select('id', 'order_no', 'order_status', 'converted_sale_id')
                ->whereNull('converted_sale_id')
                ->where('order_status', '!=', 'cancelled')
                ->latest()
                ->get(),
            'products' => Product::with('unit:id,name')->select('id', 'name', 'unit_id')->orderBy('name')->get(),
            'units' => Unit::select('id', 'name')->orderBy('name')->get(),
            'defaultOrderId' => $request->query('order_id'),
        ]);
    }

    public function store(OrderDetailRequest $request)
    {
        $order = Order::findOrFail($request->order_id);

        if ($order->converted_sale_id || $order->order_status === 'cancelled') {
            return redirect()->back()->withInput()->with('error', 'Cannot add items to a converted or cancelled order.');
        }

        $lineTotal = ((float) $request->quantity * (float) $request->unit_price)
            - (float) $request->discount + (float) $request->tax;

        try {
            DB::transaction(function () use ($request, $lineTotal, $order) {
                $trashed = OrderDetail::onlyTrashed()
                    ->where('order_id', $request->order_id)
                    ->where('product_id', $request->product_id)
                    ->first();

                if ($trashed) {
                    $trashed->restore();
                    $trashed->update([
                        'user_id' => Auth::id(),
                        'unit_id' => $request->unit_id,
                        'quantity' => $request->quantity,
                        'unit_price' => $request->unit_price,
                        'discount' => $request->discount,
                        'tax' => $request->tax,
                        'line_total' => $lineTotal,
                        'remarks' => $request->remarks,
                        'status' => $request->boolean('status', true),
                    ]);
                } else {
                    OrderDetail::create([
                        'user_id' => Auth::id(),
                        'order_id' => $request->order_id,
                        'product_id' => $request->product_id,
                        'unit_id' => $request->unit_id,
                        'quantity' => $request->quantity,
                        'unit_price' => $request->unit_price,
                        'discount' => $request->discount,
                        'tax' => $request->tax,
                        'line_total' => $lineTotal,
                        'remarks' => $request->remarks,
                        'status' => $request->boolean('status', true),
                    ]);
                }

                $this->conversion->recalcOrderTotals($order->fresh());
            });
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Could not save order item: ' . $e->getMessage());
        }

        return redirect()->route('order-details.index')->with('success', 'Order item added successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/OrderDetails/Show', [
            'detail' => OrderDetail::with(['order.customer', 'order.warehouse', 'product', 'unit', 'user'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        $detail = OrderDetail::with('order')->findOrFail($id);

        if ($detail->order?->converted_sale_id) {
            return redirect()->route('order-details.show', $detail->id)
                ->with('error', 'Converted order line items cannot be edited.');
        }

        return Inertia::render('InventoryManagement/OrderDetails/Edit', [
            'detail' => $detail,
            'orders' => Order::select('id', 'order_no', 'order_status', 'converted_sale_id')
                ->whereNull('converted_sale_id')
                ->where('order_status', '!=', 'cancelled')
                ->latest()
                ->get(),
            'products' => Product::with('unit:id,name')->select('id', 'name', 'unit_id')->orderBy('name')->get(),
            'units' => Unit::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(OrderDetailRequest $request, string $id)
    {
        $detail = OrderDetail::findOrFail($id);
        $order = Order::findOrFail($request->order_id);

        if ($order->converted_sale_id || $order->order_status === 'cancelled') {
            return redirect()->back()->withInput()->with('error', 'Cannot update items on a converted or cancelled order.');
        }

        $lineTotal = ((float) $request->quantity * (float) $request->unit_price)
            - (float) $request->discount + (float) $request->tax;

        try {
            DB::transaction(function () use ($request, $detail, $order, $lineTotal) {
                $detail->update([
                    'order_id' => $request->order_id,
                    'product_id' => $request->product_id,
                    'unit_id' => $request->unit_id,
                    'quantity' => $request->quantity,
                    'unit_price' => $request->unit_price,
                    'discount' => $request->discount,
                    'tax' => $request->tax,
                    'line_total' => $lineTotal,
                    'remarks' => $request->remarks,
                    'status' => $request->boolean('status', true),
                ]);

                $this->conversion->recalcOrderTotals($order->fresh());
            });
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Could not update order item: ' . $e->getMessage());
        }

        return redirect()->route('order-details.index')->with('success', 'Order item updated successfully');
    }

    public function destroy(string $id)
    {
        $detail = OrderDetail::with('order')->findOrFail($id);

        if ($detail->order?->converted_sale_id) {
            return redirect()->back()->with('error', 'Converted order line items cannot be deleted.');
        }

        DB::transaction(function () use ($detail) {
            $order = $detail->order;
            $detail->delete();
            if ($order) {
                $this->conversion->recalcOrderTotals($order->fresh());
            }
        });

        return redirect()->back()->with('success', 'Order item removed successfully');
    }
}
