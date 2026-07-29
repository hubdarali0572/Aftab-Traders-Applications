<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderReturn;
use App\Models\OrderReturnDetail;
use App\Services\Reports\ReportExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class OrderHistoryService
{
    public function __construct(
        protected ReportExportService $export
    ) {
    }

    public function filterKeys(): array
    {
        return [
            'date_from',
            'date_to',
            'customer_id',
            'product_id',
            'warehouse_id',
            'orders_sort',
            'orders_direction',
            'returns_sort',
            'returns_direction',
        ];
    }

    public function filters(Request $request): array
    {
        return $request->only($this->filterKeys());
    }

    public function dashboardSummary(Request $request): array
    {
        $ordersQuery = $this->ordersQuery($request);
        $returnsQuery = $this->returnsQuery($request);

        $completedOrders = (clone $ordersQuery)->where('order_status', 'completed');

        $totalOrderAmount = round((float) (clone $completedOrders)->sum('grand_total'), 2);
        $totalReturnAmount = round((float) (clone $returnsQuery)->where('return_status', 'completed')->sum('total_amount'), 2);

        $completedOrderIds = (clone $completedOrders)->pluck('id');
        $returnIds = (clone $returnsQuery)->where('return_status', 'completed')->pluck('id');

        $productsOrdered = round((float) OrderDetail::query()
            ->whereIn('order_id', $completedOrderIds)
            ->sum('quantity'), 2);

        $productsReturned = round((float) OrderReturnDetail::query()
            ->whereIn('order_return_id', $returnIds)
            ->sum('quantity'), 2);

        return [
            'total_orders' => (clone $ordersQuery)->count(),
            'total_order_amount' => $totalOrderAmount,
            'total_order_returns' => (clone $returnsQuery)->count(),
            'total_return_amount' => $totalReturnAmount,
            'net_order_amount' => round($totalOrderAmount - $totalReturnAmount, 2),
            'products_ordered' => $productsOrdered,
            'products_returned' => $productsReturned,
        ];
    }

    public function ordersQuery(Request $request): Builder
    {
        $query = Order::query();
        $this->applySharedFilters($query, $request, 'order');

        return $query;
    }

    public function returnsQuery(Request $request): Builder
    {
        $query = OrderReturn::query();
        $this->applySharedFilters($query, $request, 'return');

        return $query;
    }

    public function paginateOrders(Request $request, int $perPage = 5)
    {
        $sort = $request->input('orders_sort', 'order_date');
        $direction = $request->input('orders_direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['order_date', 'order_no', 'grand_total', 'paid_amount', 'due_amount', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'order_date';
        }

        return $this->ordersQuery($request)
            ->with([
                'customer:id,customer_name,customer_code',
                'warehouse:id,name',
                'user:id,name',
                'details.product:id,name,sku',
            ])
            ->withSum('details as total_quantity_sum', 'quantity')
            ->orderBy($sort, $direction)
            ->when($sort !== 'id', fn (Builder $q) => $q->orderByDesc('id'))
            ->paginate($perPage, ['*'], 'orders_page')
            ->withQueryString()
            ->through(fn (Order $order) => $this->transformOrder($order));
    }

    public function paginateReturns(Request $request, int $perPage = 5)
    {
        $sort = $request->input('returns_sort', 'return_date');
        $direction = $request->input('returns_direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['return_date', 'reference_no', 'total_amount', 'total_quantity', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'return_date';
        }

        return $this->returnsQuery($request)
            ->with([
                'order:id,order_no',
                'customer:id,customer_name,customer_code',
                'warehouse:id,name',
                'user:id,name',
                'details.product:id,name,sku',
            ])
            ->orderBy($sort, $direction)
            ->when($sort !== 'id', fn (Builder $q) => $q->orderByDesc('id'))
            ->paginate($perPage, ['*'], 'returns_page')
            ->withQueryString()
            ->through(fn (OrderReturn $return) => $this->transformReturn($return));
    }

    public function ordersExportRows(Request $request): Collection
    {
        return $this->ordersQuery($request)
            ->with(['customer:id,customer_name', 'warehouse:id,name', 'user:id,name', 'details.product:id,name'])
            ->withSum('details as total_quantity_sum', 'quantity')
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Order $order) => $this->transformOrder($order))
            ->map(fn (array $row) => [
                $row['datetime'],
                $row['order_no'],
                $row['customer_name'],
                $row['warehouse_name'],
                $row['products_label'],
                $row['total_quantity'],
                $row['grand_total'],
                $row['paid_amount'],
                $row['due_amount'],
                $row['processed_by'],
                $row['order_status'],
                $row['payment_status'],
            ]);
    }

    public function returnsExportRows(Request $request): Collection
    {
        return $this->returnsQuery($request)
            ->with(['order:id,order_no', 'customer:id,customer_name', 'warehouse:id,name', 'user:id,name', 'details.product:id,name'])
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (OrderReturn $return) => $this->transformReturn($return))
            ->map(fn (array $row) => [
                $row['datetime'],
                $row['reference_no'],
                $row['order_no'],
                $row['customer_name'],
                $row['warehouse_name'],
                $row['products_label'],
                $row['total_quantity'],
                $row['total_amount'],
                $row['return_reason'],
                $row['processed_by'],
            ]);
    }

    protected function applySharedFilters(Builder $query, Request $request, string $context): void
    {
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('product_id')) {
            $query->whereHas('details', fn (Builder $q) => $q->where('product_id', $request->product_id));
        }

        if ($context === 'order') {
            $this->export->applyDateRange($query, $request, 'order_date');
        }

        if ($context === 'return') {
            $this->export->applyDateRange($query, $request, 'return_date');
        }
    }

    protected function transformOrder(Order $order): array
    {
        $products = $order->details
            ->map(fn ($d) => $d->product?->name ?? 'Product #' . $d->product_id)
            ->filter()
            ->unique()
            ->values();

        return [
            'id' => $order->id,
            'datetime' => $order->created_at?->format('Y-m-d H:i') ?? $order->order_date?->format('Y-m-d'),
            'order_date' => $order->order_date?->format('Y-m-d'),
            'order_no' => $order->order_no,
            'customer_name' => $order->customer?->customer_name ?? '—',
            'customer_id' => $order->customer_id,
            'warehouse_name' => $order->warehouse?->name ?? '—',
            'products_label' => $this->productSummary($products),
            'product_count' => $products->count(),
            'total_quantity' => round((float) ($order->total_quantity_sum ?? $order->details->sum('quantity')), 2),
            'grand_total' => round((float) $order->grand_total, 2),
            'paid_amount' => round((float) ($order->paid_amount ?? 0), 2),
            'due_amount' => round((float) ($order->due_amount ?? 0), 2),
            'order_status' => $order->order_status,
            'payment_status' => $order->payment_status ?? $this->export->paymentStatus(
                (float) ($order->paid_amount ?? 0),
                (float) ($order->due_amount ?? 0),
                (float) $order->grand_total
            ),
            'processed_by' => $order->user?->name ?? '—',
            'created_by' => $order->user?->name ?? '—',
            'created_at' => $order->created_at?->toIso8601String(),
            'updated_at' => $order->updated_at?->toIso8601String(),
        ];
    }

    protected function transformReturn(OrderReturn $orderReturn): array
    {
        $products = $orderReturn->details
            ->map(fn ($d) => $d->product?->name ?? 'Product #' . $d->product_id)
            ->filter()
            ->unique()
            ->values();

        $lineReasons = $orderReturn->details->pluck('reason')->filter()->unique()->values();

        return [
            'id' => $orderReturn->id,
            'datetime' => $orderReturn->created_at?->format('Y-m-d H:i') ?? $orderReturn->return_date?->format('Y-m-d'),
            'return_date' => $orderReturn->return_date?->format('Y-m-d'),
            'reference_no' => $orderReturn->reference_no,
            'order_no' => $orderReturn->order?->order_no ?? '—',
            'order_id' => $orderReturn->order_id,
            'customer_name' => $orderReturn->customer?->customer_name ?? '—',
            'customer_id' => $orderReturn->customer_id,
            'warehouse_name' => $orderReturn->warehouse?->name ?? '—',
            'products_label' => $this->productSummary($products),
            'product_count' => $products->count(),
            'total_quantity' => round((float) $orderReturn->total_quantity, 2),
            'total_amount' => round((float) $orderReturn->total_amount, 2),
            'return_reason' => $orderReturn->return_reason ?: ($lineReasons->isNotEmpty() ? $lineReasons->join(', ') : ($orderReturn->remarks ?: '—')),
            'return_status' => $orderReturn->return_status,
            'processed_by' => $orderReturn->user?->name ?? '—',
            'created_by' => $orderReturn->user?->name ?? '—',
            'created_at' => $orderReturn->created_at?->toIso8601String(),
            'updated_at' => $orderReturn->updated_at?->toIso8601String(),
        ];
    }

    protected function productSummary(Collection $products): string
    {
        if ($products->isEmpty()) {
            return '—';
        }

        if ($products->count() === 1) {
            return (string) $products->first();
        }

        return $products->first() . ' +' . ($products->count() - 1) . ' more';
    }

    public function filterOptions(): array
    {
        return [
            'customers' => Customer::orderBy('customer_name')->get(['id', 'customer_name', 'customer_code']),
            'warehouses' => \App\Models\Warehouse::orderBy('name')->get(['id', 'name']),
            'products' => \App\Models\Product::orderBy('name')->limit(500)->get(['id', 'name', 'sku']),
        ];
    }
}
