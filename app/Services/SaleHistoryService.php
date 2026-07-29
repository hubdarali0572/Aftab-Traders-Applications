<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleReturn;
use App\Models\SaleReturnDetail;
use App\Services\Reports\ReportExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SaleHistoryService
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
            'sales_sort',
            'sales_direction',
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
        $salesQuery = $this->salesQuery($request);
        $returnsQuery = $this->returnsQuery($request);

        $completedSales = (clone $salesQuery)->where('sale_status', 'completed');

        $totalSalesAmount = round((float) (clone $completedSales)->sum('grand_total'), 2);
        $totalReturnAmount = round((float) (clone $returnsQuery)->sum('total_amount'), 2);

        $completedSaleIds = (clone $completedSales)->pluck('id');
        $returnIds = (clone $returnsQuery)->pluck('id');

        $productsSold = round((float) SaleDetail::query()
            ->whereIn('sale_id', $completedSaleIds)
            ->sum('quantity'), 2);

        $productsReturned = round((float) SaleReturnDetail::query()
            ->whereIn('sale_return_id', $returnIds)
            ->sum('quantity'), 2);

        return [
            'total_sales' => (clone $salesQuery)->count(),
            'total_sales_amount' => $totalSalesAmount,
            'total_sales_returns' => (clone $returnsQuery)->count(),
            'total_return_amount' => $totalReturnAmount,
            'net_sales_amount' => round($totalSalesAmount - $totalReturnAmount, 2),
            'products_sold' => $productsSold,
            'products_returned' => $productsReturned,
        ];
    }

    public function salesQuery(Request $request): Builder
    {
        $query = Sale::query();

        $this->applySharedFilters($query, $request, 'sale');

        return $query;
    }

    public function returnsQuery(Request $request): Builder
    {
        $query = SaleReturn::query();

        $this->applySharedFilters($query, $request, 'return');

        return $query;
    }

    public function paginateSales(Request $request, int $perPage = 5)
    {
        $sort = $request->input('sales_sort', 'sale_date');
        $direction = $request->input('sales_direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['sale_date', 'invoice_no', 'grand_total', 'paid_amount', 'due_amount', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'sale_date';
        }

        return $this->salesQuery($request)
            ->with([
                'customer:id,customer_name,customer_code',
                'warehouse:id,name',
                'user:id,name',
                'details.product:id,name,sku',
            ])
            ->withSum('details as total_quantity_sum', 'quantity')
            ->orderBy($sort, $direction)
            ->when($sort !== 'id', fn (Builder $q) => $q->orderByDesc('id'))
            ->paginate($perPage, ['*'], 'sales_page')
            ->withQueryString()
            ->through(fn (Sale $sale) => $this->transformSale($sale));
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
                'sale:id,invoice_no',
                'customer:id,customer_name,customer_code',
                'warehouse:id,name',
                'user:id,name',
                'details.product:id,name,sku',
            ])
            ->orderBy($sort, $direction)
            ->when($sort !== 'id', fn (Builder $q) => $q->orderByDesc('id'))
            ->paginate($perPage, ['*'], 'returns_page')
            ->withQueryString()
            ->through(fn (SaleReturn $return) => $this->transformReturn($return));
    }

    public function salesExportRows(Request $request): Collection
    {
        return $this->salesQuery($request)
            ->with(['customer:id,customer_name', 'warehouse:id,name', 'user:id,name', 'details.product:id,name'])
            ->withSum('details as total_quantity_sum', 'quantity')
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Sale $sale) => $this->transformSale($sale))
            ->map(fn (array $row) => [
                $row['datetime'],
                $row['invoice_no'],
                $row['customer_name'],
                $row['warehouse_name'],
                $row['products_label'],
                $row['total_quantity'],
                $row['grand_total'],
                $row['paid_amount'],
                $row['due_amount'],
                $row['sold_by'],
                $row['sale_status'],
                $row['payment_status'],
            ]);
    }

    public function returnsExportRows(Request $request): Collection
    {
        return $this->returnsQuery($request)
            ->with(['sale:id,invoice_no', 'customer:id,customer_name', 'warehouse:id,name', 'user:id,name', 'details.product:id,name'])
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (SaleReturn $return) => $this->transformReturn($return))
            ->map(fn (array $row) => [
                $row['datetime'],
                $row['reference_no'],
                $row['invoice_no'],
                $row['customer_name'],
                $row['warehouse_name'],
                $row['products_label'],
                $row['total_quantity'],
                $row['total_amount'],
                $row['return_reason'],
                $row['returned_by'],
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

        if ($context === 'sale') {
            $this->export->applyDateRange($query, $request, 'sale_date');
        }

        if ($context === 'return') {
            $this->export->applyDateRange($query, $request, 'return_date');
        }
    }

    protected function transformSale(Sale $sale): array
    {
        $products = $sale->details
            ->map(fn ($d) => $d->product?->name ?? 'Product #' . $d->product_id)
            ->filter()
            ->unique()
            ->values();

        return [
            'id' => $sale->id,
            'datetime' => $sale->created_at?->format('Y-m-d H:i') ?? $sale->sale_date?->format('Y-m-d'),
            'sale_date' => $sale->sale_date?->format('Y-m-d'),
            'invoice_no' => $sale->invoice_no,
            'customer_name' => $sale->customer?->customer_name ?? 'Walk-in',
            'customer_id' => $sale->customer_id,
            'warehouse_name' => $sale->warehouse?->name ?? '—',
            'products_label' => $this->productSummary($products),
            'product_count' => $products->count(),
            'total_quantity' => round((float) ($sale->total_quantity_sum ?? $sale->details->sum('quantity')), 2),
            'grand_total' => round((float) $sale->grand_total, 2),
            'paid_amount' => round((float) $sale->paid_amount, 2),
            'due_amount' => round((float) $sale->due_amount, 2),
            'sale_status' => $sale->sale_status,
            'payment_status' => $this->export->paymentStatus(
                (float) $sale->paid_amount,
                (float) $sale->due_amount,
                (float) $sale->grand_total
            ),
            'sold_by' => $sale->user?->name ?? '—',
            'created_by' => $sale->user?->name ?? '—',
            'created_at' => $sale->created_at?->toIso8601String(),
        ];
    }

    protected function transformReturn(SaleReturn $saleReturn): array
    {
        $products = $saleReturn->details
            ->map(fn ($d) => $d->product?->name ?? 'Product #' . $d->product_id)
            ->filter()
            ->unique()
            ->values();

        $reasons = $saleReturn->details->pluck('reason')->filter()->unique()->values();

        return [
            'id' => $saleReturn->id,
            'datetime' => $saleReturn->created_at?->format('Y-m-d H:i') ?? $saleReturn->return_date?->format('Y-m-d'),
            'return_date' => $saleReturn->return_date?->format('Y-m-d'),
            'reference_no' => $saleReturn->reference_no,
            'invoice_no' => $saleReturn->sale?->invoice_no ?? '—',
            'sale_id' => $saleReturn->sale_id,
            'customer_name' => $saleReturn->customer?->customer_name ?? 'Walk-in',
            'customer_id' => $saleReturn->customer_id,
            'warehouse_name' => $saleReturn->warehouse?->name ?? '—',
            'products_label' => $this->productSummary($products),
            'product_count' => $products->count(),
            'total_quantity' => round((float) $saleReturn->total_quantity, 2),
            'total_amount' => round((float) $saleReturn->total_amount, 2),
            'return_reason' => $reasons->isNotEmpty() ? $reasons->join(', ') : ($saleReturn->remarks ?: '—'),
            'returned_by' => $saleReturn->user?->name ?? '—',
            'created_by' => $saleReturn->user?->name ?? '—',
            'created_at' => $saleReturn->created_at?->toIso8601String(),
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
