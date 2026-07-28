<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Warehouse;
use App\Services\OrderConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;
use Throwable;

class OrderController extends Controller
{
    protected array $orderTypes = ['wholesale', 'retail'];

    protected array $orderStatuses = [
        'pending', 'confirmed', 'processing', 'completed', 'cancelled',
    ];

    public function __construct(
        protected OrderConversionService $conversion
    ) {
    }

    public function index(Request $request)
    {
        $orders = Order::query()
            ->with(['customer', 'warehouse', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('order_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('customer_name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('order_status'), fn ($q) => $q->where('order_status', $request->order_status))
            ->when($request->filled('order_type'), fn ($q) => $q->where('order_type', $request->order_type))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only('search', 'order_status', 'order_type'),
            'orderStatuses' => $this->orderStatuses,
            'orderTypes' => $this->orderTypes,
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/Orders/Create', [
            'customers' => Customer::select('id', 'customer_name', 'customer_code')->orderBy('customer_name')->get(),
            'warehouses' => Warehouse::select('id', 'name')->get(),
            'orderTypes' => $this->orderTypes,
            'orderStatuses' => $this->orderStatuses,
        ]);
    }

    public function store(OrderRequest $request)
    {
        $order = Order::create(array_merge($request->validated(), [
            'user_id' => Auth::id(),
            'status' => $request->boolean('status', true),
            'subtotal' => 0,
            'converted_sale_id' => null,
            'grand_total' => max(
                0,
                0 - (float) $request->discount + (float) $request->tax + (float) $request->other_charges
            ),
        ]));

        return redirect()
            ->route('order-details.create', ['order_id' => $order->id])
            ->with('success', 'Order saved. Now add product line items — subtotal updates automatically.');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/Orders/Show', [
            'order' => Order::with([
                'customer',
                'warehouse',
                'user',
                'details.product',
                'details.unit',
                'convertedSale:id,invoice_no',
            ])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        $order = Order::findOrFail($id);

        if ($order->converted_sale_id) {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'This order has been converted to a sale and can no longer be edited.');
        }

        return Inertia::render('InventoryManagement/Orders/Edit', [
            'order' => $order,
            'customers' => Customer::select('id', 'customer_name', 'customer_code')->orderBy('customer_name')->get(),
            'warehouses' => Warehouse::select('id', 'name')->get(),
            'orderTypes' => $this->orderTypes,
            'orderStatuses' => $this->orderStatuses,
        ]);
    }

    public function update(OrderRequest $request, string $id)
    {
        $order = Order::findOrFail($id);

        if ($order->converted_sale_id) {
            return redirect()->back()->with('error', 'Converted orders cannot be updated.');
        }

        $order->update(array_merge($request->validated(), [
            'status' => $request->boolean('status', true),
        ]));

        $this->conversion->recalcOrderTotals($order->fresh());

        return redirect()->route('orders.index')->with('success', 'Order updated successfully');
    }

    public function destroy(string $id)
    {
        $order = Order::with('details')->findOrFail($id);

        if ($order->converted_sale_id) {
            return redirect()->back()->with('error', 'Converted orders cannot be deleted.');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->details as $detail) {
                $detail->delete();
            }
            $order->delete();
        });

        return redirect()->back()->with('success', 'Order deleted successfully');
    }

    public function convertToSale(string $id)
    {
        $order = Order::with('details')->findOrFail($id);

        try {
            $sale = DB::transaction(fn () => $this->conversion->convertOrderToSale($order));
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Could not convert order: ' . $e->getMessage());
        }

        return redirect()->route('sales.show', $sale->id)
            ->with('success', 'Order converted to sales invoice ' . $sale->invoice_no . ' successfully');
    }
}
