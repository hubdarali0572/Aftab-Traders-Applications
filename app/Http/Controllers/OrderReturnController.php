<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderReturn;
use App\Models\OrderReturnDetail;
use App\Services\InventoryPostingService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;

class OrderReturnController extends Controller
{
    protected array $returnStatuses = ['pending', 'completed', 'cancelled'];

    public function __construct(
        protected InventoryPostingService $posting,
        protected OrderService $orderService
    ) {
    }

    public function index(Request $request)
    {
        $filters = $request->only('search', 'return_status');

        $returns = OrderReturn::query()
            ->with(['order', 'customer', 'warehouse', 'user'])
            ->tap(fn ($q) => $this->orderService->applyReturnFilters($q, $filters))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/OrderReturns/Index', [
            'returns' => $returns,
            'summary' => $this->orderService->returnsDashboardSummary($filters),
            'filters' => $filters,
            'returnStatuses' => $this->returnStatuses,
        ]);
    }

    public function create(Request $request)
    {
        $selectedOrder = null;
        if ($request->filled('order_id')) {
            $selectedOrder = $this->ordersForForms()->firstWhere('id', (int) $request->order_id);
        }

        return Inertia::render('InventoryManagement/OrderReturns/Create', [
            'orders' => $this->ordersForForms(),
            'selectedOrder' => $selectedOrder,
            'generatedReferenceNo' => $this->orderService->generateReturnReferenceNo(),
            'returnStatuses' => $this->returnStatuses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge($this->headerRules(), $this->itemRules()));

        $order = Order::with('details.product')
            ->where('order_status', 'completed')
            ->findOrFail($validated['order_id']);

        try {
            DB::transaction(function () use ($request, $validated, $order) {
                $orderReturn = OrderReturn::create([
                    'user_id' => Auth::id(),
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'warehouse_id' => $order->warehouse_id,
                    'reference_no' => $validated['reference_no'],
                    'return_date' => $validated['return_date'],
                    'return_reason' => $validated['return_reason'] ?? null,
                    'total_quantity' => 0,
                    'total_amount' => 0,
                    'return_status' => $validated['return_status'],
                    'remarks' => $validated['remarks'] ?? null,
                    'status' => $request->boolean('status', true),
                ]);

                $this->syncReturnItems($orderReturn, $request->items, $order);
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('order-returns.index')->with('success', 'Order return recorded successfully');
    }

    public function show(string $id)
    {
        $orderReturn = OrderReturn::with([
            'order:id,order_no,order_date',
            'customer:id,customer_name,customer_code,phone',
            'warehouse:id,name',
            'user:id,name,email',
            'details.product:id,name,sku',
            'details.unit:id,name',
        ])->findOrFail($id);

        return Inertia::render('InventoryManagement/OrderReturns/Show', [
            'orderReturn' => $orderReturn,
            'summary' => [
                'total_items' => $orderReturn->details->count(),
                'total_quantity' => (float) $orderReturn->total_quantity,
                'total_amount' => (float) $orderReturn->total_amount,
            ],
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/OrderReturns/Edit', [
            'orderReturn' => OrderReturn::with('details.product', 'details.unit')->findOrFail($id),
            'orders' => $this->ordersForForms(null, (int) $id),
            'returnStatuses' => $this->returnStatuses,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $orderReturn = OrderReturn::with('details')->findOrFail($id);
        $validated = $request->validate(array_merge(
            $this->headerRules($orderReturn->id),
            $this->itemRules()
        ));

        $order = Order::with('details.product')
            ->where('order_status', 'completed')
            ->findOrFail($validated['order_id']);

        if ($orderReturn->details->isNotEmpty() && (int) $orderReturn->order_id !== (int) $order->id) {
            return redirect()->back()->withInput()->with('error', 'You cannot change the order once return line items exist.');
        }

        $mustResync = $orderReturn->return_status !== $validated['return_status']
            || ($validated['return_status'] === 'completed' && (
                $orderReturn->warehouse_id != $order->warehouse_id
                || $orderReturn->return_date->format('Y-m-d') !== $validated['return_date']
                || $orderReturn->reference_no !== $validated['reference_no']
            ));

        try {
            DB::transaction(function () use ($request, $orderReturn, $validated, $order, $mustResync) {
                $orderReturn->update([
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'warehouse_id' => $order->warehouse_id,
                    'reference_no' => $validated['reference_no'],
                    'return_date' => $validated['return_date'],
                    'return_reason' => $validated['return_reason'] ?? null,
                    'return_status' => $validated['return_status'],
                    'remarks' => $validated['remarks'] ?? null,
                    'status' => $request->boolean('status', true),
                ]);

                $this->syncReturnItems($orderReturn->fresh(), $request->items, $order, $orderReturn->id);

                if ($mustResync || $validated['return_status'] === 'completed') {
                    $this->posting->syncOrderReturnStock($orderReturn->fresh(['details']));
                }
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('order-returns.index')->with('success', 'Order return updated successfully');
    }

    public function destroy(string $id)
    {
        $orderReturn = OrderReturn::with('details')->findOrFail($id);

        try {
            DB::transaction(function () use ($orderReturn) {
                foreach ($orderReturn->details as $detail) {
                    $this->posting->reverseOrderReturnDetail($detail);
                    $detail->delete();
                }

                $orderReturn->delete();
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Order return deleted successfully');
    }

    protected function headerRules(?int $ignoreId = null): array
    {
        $uniqueRule = 'required|string|unique:order_returns,reference_no';
        if ($ignoreId) {
            $uniqueRule .= ',' . $ignoreId;
        }

        return [
            'reference_no' => $uniqueRule,
            'order_id' => 'required|exists:orders,id',
            'return_date' => 'required|date',
            'return_reason' => 'nullable|string',
            'return_status' => 'required|in:' . implode(',', $this->returnStatuses),
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

    protected function syncReturnItems(OrderReturn $orderReturn, array $items, Order $order, ?int $ignoreReturnId = null): void
    {
        foreach ($orderReturn->details as $detail) {
            $this->posting->reverseOrderReturnDetail($detail);
        }
        $orderReturn->details()->delete();

        foreach ($items as $row) {
            $orderDetail = $this->getOriginalOrderDetail($order, (int) $row['product_id']);

            if (! $orderDetail) {
                throw new InvalidArgumentException('Selected product does not exist on the referenced order.');
            }

            $returnQty = (float) $row['quantity'];
            $this->ensureReturnQuantityAllowed($order, (int) $row['product_id'], $returnQty, $ignoreReturnId);

            $pricing = $this->deriveReturnAmounts($orderDetail, $returnQty);

            $detail = $orderReturn->details()->create([
                'user_id' => Auth::id(),
                'product_id' => $row['product_id'],
                'unit_id' => $orderDetail->unit_id,
                'quantity' => $returnQty,
                'unit_price' => $pricing['unit_price'],
                'line_total' => $pricing['line_total'],
                'reason' => $row['reason'] ?? null,
                'remarks' => $row['remarks'] ?? null,
                'status' => true,
            ]);

            $this->posting->postOrderReturnDetail($detail);
        }
    }

    protected function ordersForForms(?int $onlyOrderId = null, ?int $ignoreReturnId = null)
    {
        $query = Order::with([
            'customer:id,customer_name',
            'warehouse:id,name',
            'details:id,order_id,product_id,unit_id,quantity,unit_price,discount,tax',
            'details.product:id,name,sku',
            'details.unit:id,name,short_name',
        ])
            ->select('id', 'order_no', 'customer_id', 'warehouse_id', 'order_status')
            ->where('order_status', 'completed')
            ->orderByDesc('id');

        if ($onlyOrderId) {
            $query->where('id', $onlyOrderId);
        }

        return $query->get()
            ->map(function (Order $order) use ($ignoreReturnId) {
                $returnedByProduct = OrderReturnDetail::query()
                    ->whereHas('orderReturn', function ($q) use ($order, $ignoreReturnId) {
                        $q->where('order_id', $order->id)
                            ->where('return_status', '!=', 'cancelled');
                        if ($ignoreReturnId) {
                            $q->where('id', '!=', $ignoreReturnId);
                        }
                    })
                    ->selectRaw('product_id, SUM(quantity) as returned_qty')
                    ->groupBy('product_id')
                    ->pluck('returned_qty', 'product_id');

                $order->details->transform(function ($detail) use ($returnedByProduct) {
                    $ordered = (float) $detail->quantity;
                    $returned = (float) ($returnedByProduct[$detail->product_id] ?? 0);
                    $detail->returnable_qty = max(0, $ordered - $returned);

                    return $detail;
                });

                return $order;
            });
    }

    protected function getOriginalOrderDetail(Order $order, int $productId): ?OrderDetail
    {
        return $order->details->firstWhere('product_id', $productId)
            ?? OrderDetail::query()
                ->where('order_id', $order->id)
                ->where('product_id', $productId)
                ->first();
    }

    protected function ensureReturnQuantityAllowed(
        Order $order,
        int $productId,
        float $qty,
        ?int $ignoreReturnId = null
    ): void {
        $orderedQty = (float) OrderDetail::where('order_id', $order->id)
            ->where('product_id', $productId)
            ->value('quantity');

        $returnedQty = (float) OrderReturnDetail::query()
            ->where('product_id', $productId)
            ->whereHas('orderReturn', function ($q) use ($order, $ignoreReturnId) {
                $q->where('order_id', $order->id)
                    ->where('return_status', '!=', 'cancelled');
                if ($ignoreReturnId) {
                    $q->where('id', '!=', $ignoreReturnId);
                }
            })
            ->sum('quantity');

        if ($qty + $returnedQty > $orderedQty) {
            throw new InvalidArgumentException(
                'Return quantity exceeds the remaining ordered quantity. Ordered: ' . number_format($orderedQty, 2) .
                ', already returned: ' . number_format($returnedQty, 2) . '.'
            );
        }
    }

    protected function deriveReturnAmounts(OrderDetail $orderDetail, float $qty): array
    {
        $baseQty = max((float) $orderDetail->quantity, 0.01);
        $unitDiscount = (float) $orderDetail->discount / $baseQty;
        $unitTax = (float) $orderDetail->tax / $baseQty;
        $unitPrice = (float) $orderDetail->unit_price;
        $lineTotal = round(($qty * $unitPrice) - ($unitDiscount * $qty) + ($unitTax * $qty), 2);

        return [
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
        ];
    }
}
