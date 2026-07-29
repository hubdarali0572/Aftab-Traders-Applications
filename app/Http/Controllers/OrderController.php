<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CustomerService;
use App\Services\InventoryPostingService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;

class OrderController extends Controller
{
    protected array $orderStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];

    public function __construct(
        protected InventoryPostingService $posting,
        protected CustomerService $customerService,
        protected OrderService $orderService
    ) {
    }

    public function index(Request $request)
    {
        $filters = $request->only('search', 'order_status');

        $orders = Order::query()
            ->with(['customer', 'warehouse', 'user'])
            ->tap(fn ($q) => $this->orderService->applyOrderFilters($q, $filters))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/Orders/Index', [
            'orders' => $orders,
            'summary' => $this->orderService->ordersDashboardSummary($filters),
            'filters' => $filters,
            'orderStatuses' => $this->orderStatuses,
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/Orders/Create', [
            'customers' => Customer::where('status', true)->select('id', 'customer_name', 'customer_code')->orderBy('customer_name')->get(),
            'warehouses' => Warehouse::select('id', 'name')->orderBy('name')->get(),
            'products' => Product::select('id', 'name', 'sku', 'selling_price')->orderBy('name')->get(),
            'units' => Unit::select('id', 'name', 'short_name')->orderBy('name')->get(),
            'users' => User::select('id', 'name')->orderBy('name')->get(),
            'warehouseStocks' => Stock::select('warehouse_id', 'product_id', 'quantity')->get(),
            'orderStatuses' => $this->orderStatuses,
            'generatedOrderNo' => $this->orderService->generateOrderNo(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge($this->headerRules(), $this->itemRules()));

        $this->customerService->assertActiveForTransaction((int) $validated['customer_id']);

        try {
            DB::transaction(function () use ($request, $validated) {
                $order = Order::create(array_merge(
                    $this->pickHeaderFields($validated),
                    [
                        'user_id' => $request->input('processed_by_id') ?: Auth::id(),
                        'order_type' => 'retail',
                        'subtotal' => 0,
                        'grand_total' => 0,
                        'due_amount' => 0,
                        'payment_status' => 'unpaid',
                        'converted_sale_id' => null,
                        'status' => $request->boolean('status', true),
                    ]
                ));

                $this->syncOrderItems($order, $request->items);
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.index')->with('success', 'Order recorded successfully');
    }

    public function show(string $id)
    {
        $order = Order::with([
            'customer:id,customer_code,customer_name,customer_type,company_name,phone,email,city,address',
            'warehouse:id,name',
            'user:id,name,email',
            'details.product:id,name,sku',
            'details.unit:id,name,short_name',
            'orderReturns:id,order_id,reference_no,return_date,total_quantity,total_amount,return_status',
        ])->findOrFail($id);

        $lineDiscount = (float) $order->details->sum('discount');
        $lineTax = (float) $order->details->sum('tax');
        $totalQty = (float) $order->details->sum('quantity');

        return Inertia::render('InventoryManagement/Orders/Show', [
            'order' => $order,
            'summary' => [
                'total_items' => $order->details->count(),
                'total_quantity' => round($totalQty, 2),
                'line_discount' => round($lineDiscount, 2),
                'line_tax' => round($lineTax, 2),
            ],
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/Orders/Edit', [
            'order' => Order::with('details.product', 'details.unit')->findOrFail($id),
            'customers' => Customer::where('status', true)->select('id', 'customer_name', 'customer_code')->orderBy('customer_name')->get(),
            'warehouses' => Warehouse::select('id', 'name')->orderBy('name')->get(),
            'products' => Product::select('id', 'name', 'sku', 'selling_price')->orderBy('name')->get(),
            'units' => Unit::select('id', 'name', 'short_name')->orderBy('name')->get(),
            'users' => User::select('id', 'name')->orderBy('name')->get(),
            'warehouseStocks' => Stock::select('warehouse_id', 'product_id', 'quantity')->get(),
            'orderStatuses' => $this->orderStatuses,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $order = Order::with('details')->findOrFail($id);
        $validated = $request->validate(array_merge($this->headerRules($order->id), $this->itemRules()));

        $this->customerService->assertActiveForTransaction((int) $validated['customer_id']);

        $mustResyncStock = $order->order_status !== $validated['order_status']
            || ($validated['order_status'] === 'completed' && (
                $order->warehouse_id != $validated['warehouse_id']
                || $order->order_date->format('Y-m-d') !== $validated['order_date']
                || $order->order_no !== $validated['order_no']
            ));

        try {
            DB::transaction(function () use ($request, $order, $validated, $mustResyncStock) {
                $order->update(array_merge(
                    $this->pickHeaderFields($validated),
                    [
                        'user_id' => $request->input('processed_by_id') ?: $order->user_id,
                        'status' => $request->boolean('status', true),
                    ]
                ));

                $this->syncOrderItems($order->fresh(), $request->items);

                if ($mustResyncStock || $validated['order_status'] === 'completed') {
                    $this->posting->syncOrderStock($order->fresh(['details']));
                }
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.index')->with('success', 'Order updated successfully');
    }

    public function destroy(string $id)
    {
        $order = Order::with('details')->findOrFail($id);

        try {
            DB::transaction(function () use ($order) {
                $order->update(['order_status' => 'cancelled']);
                $this->posting->syncOrderStock($order->fresh(['details']));

                foreach ($order->details as $detail) {
                    $detail->delete();
                }

                $this->posting->syncOrderCustomerLedger($order->fresh());
                $order->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Order deleted successfully');
    }

    protected function headerRules(?int $ignoreId = null): array
    {
        $uniqueRule = 'required|string|unique:orders,order_no';
        if ($ignoreId) {
            $uniqueRule .= ',' . $ignoreId;
        }

        return [
            'order_no' => $uniqueRule,
            'order_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'order_status' => 'required|in:' . implode(',', $this->orderStatuses),
            'remarks' => 'nullable|string',
            'processed_by_id' => 'nullable|exists:users,id',
            'status' => 'boolean',
        ];
    }

    protected function itemRules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id|distinct',
            'items.*.unit_id' => 'required|exists:units,id',
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
            'order_no' => $data['order_no'],
            'order_date' => $data['order_date'],
            'customer_id' => $data['customer_id'],
            'warehouse_id' => $data['warehouse_id'],
            'discount' => $data['discount'] ?? 0,
            'tax' => $data['tax'] ?? 0,
            'other_charges' => $data['other_charges'] ?? 0,
            'paid_amount' => $data['paid_amount'] ?? 0,
            'order_status' => $data['order_status'],
            'remarks' => $data['remarks'] ?? null,
        ];
    }

    protected function syncOrderItems(Order $order, array $items): void
    {
        foreach ($order->details as $detail) {
            $this->posting->reverseOrderDetail($detail);
        }
        $order->details()->delete();

        foreach ($items as $row) {
            $lineTotal = $this->computeLineTotal($row);

            $trashed = OrderDetail::onlyTrashed()
                ->where('order_id', $order->id)
                ->where('product_id', $row['product_id'])
                ->first();

            if ($trashed) {
                $trashed->restore();
                $trashed->update([
                    'user_id' => Auth::id(),
                    'unit_id' => $row['unit_id'],
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
                $detail = $order->details()->create([
                    'user_id' => Auth::id(),
                    'product_id' => $row['product_id'],
                    'unit_id' => $row['unit_id'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'discount' => $row['discount'] ?? 0,
                    'tax' => $row['tax'] ?? 0,
                    'line_total' => $lineTotal,
                    'remarks' => $row['remarks'] ?? null,
                    'status' => true,
                ]);
            }

            $this->posting->postOrderDetail($detail);
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
