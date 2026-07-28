<?php

namespace App\Services\Reports;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleReturnDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    public function __construct(protected ReportExportService $export)
    {
    }

    public function daily(Request $request)
    {
        $query = Sale::query()
            ->with(['customer', 'warehouse', 'user', 'details'])
            ->where('sale_status', '!=', 'cancelled');

        $this->export->applyDateRange($query, $request, 'sale_date');

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('payment_status')) {
            $status = $request->payment_status;
            if ($status === 'paid') {
                $query->where('due_amount', '<=', 0);
            } elseif ($status === 'unpaid') {
                $query->where('paid_amount', '<=', 0)->where('due_amount', '>', 0);
            } elseif ($status === 'partial') {
                $query->where('paid_amount', '>', 0)->where('due_amount', '>', 0);
            }
        }

        $sort = $request->input('sort', 'sale_date');
        $direction = $request->input('direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $allowed = ['sale_date', 'invoice_no', 'grand_total', 'paid_amount', 'due_amount'];
        if (! in_array($sort, $allowed, true)) {
            $sort = 'sale_date';
        }
        $query->orderBy($sort, $direction)->orderByDesc('id');

        $summaryQuery = (clone $query);
        $summary = [
            'total_sales' => (float) (clone $summaryQuery)->sum('grand_total'),
            'total_discount' => (float) (clone $summaryQuery)->sum('discount'),
            'total_tax' => (float) (clone $summaryQuery)->sum('tax'),
            'total_paid' => (float) (clone $summaryQuery)->sum('paid_amount'),
            'total_outstanding' => (float) (clone $summaryQuery)->sum('due_amount'),
            'total_quantity' => (float) SaleDetail::query()
                ->whereIn('sale_id', (clone $summaryQuery)->select('id'))
                ->sum('quantity'),
            'invoice_count' => (clone $summaryQuery)->count(),
        ];

        return compact('query', 'summary');
    }

    public function mapDailyRow(Sale $sale): array
    {
        $qty = (float) $sale->details->sum('quantity');

        return [
            'id' => $sale->id,
            'invoice_no' => $sale->invoice_no,
            'invoice_date' => $sale->sale_date?->format('Y-m-d'),
            'customer' => $sale->customer?->customer_name ?? $sale->customer?->company_name,
            'sales_person' => $sale->user?->name,
            'warehouse' => $sale->warehouse?->name,
            'total_quantity' => $qty,
            'gross_amount' => (float) $sale->subtotal,
            'discount' => (float) $sale->discount,
            'tax' => (float) $sale->tax,
            'net_amount' => (float) $sale->grand_total,
            'paid_amount' => (float) $sale->paid_amount,
            'due_amount' => (float) $sale->due_amount,
            'payment_status' => $this->export->paymentStatus($sale->paid_amount, $sale->due_amount, $sale->grand_total),
            'payment_method' => $sale->payment_method,
        ];
    }

    public function monthly(Request $request): array
    {
        $query = Sale::query()->where('sale_status', '!=', 'cancelled');
        $this->export->applyDateRange($query, $request, 'sale_date');
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $rows = (clone $query)
            ->selectRaw("DATE_FORMAT(sale_date, '%Y-%m') as month")
            ->selectRaw('COUNT(*) as invoice_count')
            ->selectRaw('COUNT(DISTINCT customer_id) as customer_count')
            ->selectRaw('SUM(subtotal) as gross_sales')
            ->selectRaw('SUM(discount) as discounts')
            ->selectRaw('SUM(tax) as taxes')
            ->selectRaw('SUM(grand_total) as net_sales')
            ->selectRaw('SUM(paid_amount) as payments_received')
            ->selectRaw('SUM(due_amount) as outstanding')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $saleIdsByMonth = (clone $query)
            ->selectRaw("DATE_FORMAT(sale_date, '%Y-%m') as month, id")
            ->get()
            ->groupBy('month');

        $mapped = $rows->map(function ($row) use ($saleIdsByMonth) {
            $ids = $saleIdsByMonth->get($row->month, collect())->pluck('id');
            $qty = $ids->isEmpty() ? 0 : (float) SaleDetail::whereIn('sale_id', $ids)->sum('quantity');

            return [
                'month' => $row->month,
                'label' => date('M Y', strtotime($row->month . '-01')),
                'invoice_count' => (int) $row->invoice_count,
                'customer_count' => (int) $row->customer_count,
                'total_quantity' => $qty,
                'gross_sales' => (float) $row->gross_sales,
                'discounts' => (float) $row->discounts,
                'taxes' => (float) $row->taxes,
                'net_sales' => (float) $row->net_sales,
                'payments_received' => (float) $row->payments_received,
                'outstanding' => (float) $row->outstanding,
            ];
        })->values();

        $summary = [
            'total_sales' => (float) $mapped->sum('net_sales'),
            'total_invoices' => (int) $mapped->sum('invoice_count'),
            'total_quantity' => (float) $mapped->sum('total_quantity'),
            'total_paid' => (float) $mapped->sum('payments_received'),
            'total_outstanding' => (float) $mapped->sum('outstanding'),
        ];

        return ['rows' => $mapped, 'summary' => $summary, 'chart' => $mapped];
    }

    public function customerWise(Request $request): array
    {
        $sales = Sale::query()
            ->where('sale_status', '!=', 'cancelled')
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id));

        $this->export->applyDateRange($sales, $request, 'sale_date');

        $rows = (clone $sales)
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->selectRaw('customers.id as customer_id')
            ->selectRaw('customers.customer_name')
            ->selectRaw('customers.company_name')
            ->selectRaw('customers.customer_code')
            ->selectRaw('COUNT(sales.id) as invoice_count')
            ->selectRaw('SUM(sales.grand_total) as total_sales')
            ->selectRaw('SUM(sales.paid_amount) as paid_amount')
            ->selectRaw('SUM(sales.due_amount) as remaining_balance')
            ->selectRaw('MAX(sales.sale_date) as last_purchase_date')
            ->groupBy('customers.id', 'customers.customer_name', 'customers.company_name', 'customers.customer_code')
            ->orderByDesc('total_sales')
            ->get();

        $mapped = $rows->map(function ($row) use ($request) {
            $saleIds = Sale::query()
                ->where('customer_id', $row->customer_id)
                ->where('sale_status', '!=', 'cancelled')
                ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id));
            $this->export->applyDateRange($saleIds, $request, 'sale_date');
            $ids = $saleIds->pluck('id');

            $qty = $ids->isEmpty() ? 0 : (float) SaleDetail::whereIn('sale_id', $ids)->sum('quantity');
            $products = $ids->isEmpty() ? 0 : (int) SaleDetail::whereIn('sale_id', $ids)->distinct('product_id')->count('product_id');

            return [
                'customer_id' => $row->customer_id,
                'customer' => $row->customer_name ?: $row->company_name,
                'customer_code' => $row->customer_code,
                'invoice_count' => (int) $row->invoice_count,
                'total_products' => $products,
                'total_quantity' => $qty,
                'total_sales' => (float) $row->total_sales,
                'paid_amount' => (float) $row->paid_amount,
                'remaining_balance' => (float) $row->remaining_balance,
                'last_purchase_date' => $row->last_purchase_date,
            ];
        })->values();

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $mapped = $mapped->filter(fn ($r) => str_contains(strtolower($r['customer'] ?? ''), $search)
                || str_contains(strtolower($r['customer_code'] ?? ''), $search))->values();
        }

        $summary = [
            'customers' => $mapped->count(),
            'total_sales' => (float) $mapped->sum('total_sales'),
            'total_paid' => (float) $mapped->sum('paid_amount'),
            'total_outstanding' => (float) $mapped->sum('remaining_balance'),
            'total_quantity' => (float) $mapped->sum('total_quantity'),
        ];

        return ['rows' => $mapped, 'summary' => $summary];
    }

    public function productWise(Request $request): array
    {
        $query = SaleDetail::query()
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->whereNull('sales.deleted_at')
            ->whereNull('sale_details.deleted_at')
            ->where('sales.sale_status', '!=', 'cancelled');

        $this->export->applyDateRange($query, $request, 'sales.sale_date');

        if ($request->filled('warehouse_id')) {
            $query->where('sales.warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('category_id')) {
            $query->where('products.product_category_id', $request->category_id);
        }
        if ($request->filled('brand_id')) {
            $query->where('products.brand_id', $request->brand_id);
        }
        if ($request->filled('product_id')) {
            $query->where('sale_details.product_id', $request->product_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.sku', 'like', "%{$search}%");
            });
        }

        $rows = $query
            ->selectRaw('products.id as product_id')
            ->selectRaw('products.name as product')
            ->selectRaw('products.sku')
            ->selectRaw('COALESCE(product_categories.name, "Uncategorized") as category')
            ->selectRaw('COALESCE(brands.name, "—") as brand')
            ->selectRaw('SUM(sale_details.quantity) as quantity_sold')
            ->selectRaw('SUM(sale_details.line_total) as sales_amount')
            ->selectRaw('AVG(sale_details.unit_price) as avg_selling_price')
            ->groupBy('products.id', 'products.name', 'products.sku', 'product_categories.name', 'brands.name')
            ->orderByDesc('quantity_sold')
            ->get();

        $returns = SaleReturnDetail::query()
            ->join('sale_returns', 'sale_return_details.sale_return_id', '=', 'sale_returns.id')
            ->whereNull('sale_returns.deleted_at')
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('sale_returns.warehouse_id', $request->warehouse_id));

        $this->export->applyDateRange($returns, $request, 'sale_returns.return_date');

        $returnMap = $returns
            ->selectRaw('sale_return_details.product_id, SUM(sale_return_details.quantity) as qty')
            ->groupBy('sale_return_details.product_id')
            ->pluck('qty', 'product_id');

        $mapped = $rows->map(function ($row) use ($returnMap) {
            $ret = (float) ($returnMap[$row->product_id] ?? 0);
            $qty = (float) $row->quantity_sold;

            return [
                'product_id' => $row->product_id,
                'product' => $row->product,
                'sku' => $row->sku,
                'category' => $row->category,
                'brand' => $row->brand,
                'quantity_sold' => $qty,
                'sales_amount' => (float) $row->sales_amount,
                'avg_selling_price' => (float) $row->avg_selling_price,
                'sales_returns' => $ret,
                'net_quantity_sold' => $qty - $ret,
            ];
        })->values();

        $summary = [
            'products' => $mapped->count(),
            'quantity_sold' => (float) $mapped->sum('quantity_sold'),
            'sales_amount' => (float) $mapped->sum('sales_amount'),
            'sales_returns' => (float) $mapped->sum('sales_returns'),
            'net_quantity' => (float) $mapped->sum('net_quantity_sold'),
        ];

        return ['rows' => $mapped, 'summary' => $summary];
    }
}
