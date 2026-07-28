<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\DamagedStock;
use App\Models\OpeningStock;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseExpense;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleReturn;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalInventoryQty = (float) WarehouseStock::sum('quantity');
        $inventoryValue = (float) WarehouseStock::sum('stock_value');

        $purchaseAmount = (float) Purchase::where('purchase_status', '!=', 'cancelled')->sum('grand_total');
        $salesAmount = (float) Sale::where('sale_status', 'completed')->sum('grand_total');
        $purchaseExpenseTotal = (float) PurchaseExpense::sum('amount');
        $purchaseReturnAmount = (float) PurchaseReturn::sum('total_amount');
        $saleReturnAmount = (float) SaleReturn::sum('total_amount');

        // COGS approximation: sum of sale line quantities × warehouse average cost
        $cogs = (float) SaleDetail::query()
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('warehouse_stocks', function ($join) {
                $join->on('warehouse_stocks.product_id', '=', 'sale_details.product_id')
                    ->on('warehouse_stocks.warehouse_id', '=', 'sales.warehouse_id');
            })
            ->where('sales.sale_status', 'completed')
            ->selectRaw('COALESCE(SUM(sale_details.quantity * warehouse_stocks.average_cost), 0) as cogs')
            ->value('cogs');

        $grossProfit = $salesAmount - $cogs;
        $netProfit = $grossProfit - $purchaseExpenseTotal;

        $lowStock = WarehouseStock::with(['product', 'warehouse'])
            ->whereColumn('available_quantity', '<=', 'minimum_stock')
            ->where('available_quantity', '>', 0)
            ->orderBy('available_quantity')
            ->limit(10)
            ->get();

        $outOfStock = WarehouseStock::with(['product', 'warehouse'])
            ->where('available_quantity', '<=', 0)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        $outstandingCustomers = Customer::query()
            ->withSum('ledgers as total_debit', 'debit')
            ->withSum('ledgers as total_credit', 'credit')
            ->get()
            ->map(function ($c) {
                $c->outstanding = (float) $c->total_debit - (float) $c->total_credit;
                return $c;
            })
            ->filter(fn ($c) => $c->outstanding > 0)
            ->sortByDesc('outstanding')
            ->take(10)
            ->values();

        $allOutstanding = (float) CustomerLedger::selectRaw('COALESCE(SUM(debit - credit), 0) as bal')->value('bal');

        $monthlyPurchases = Purchase::query()
            ->selectRaw("DATE_FORMAT(purchase_date, '%Y-%m') as month, SUM(grand_total) as total")
            ->where('purchase_status', '!=', 'cancelled')
            ->where('purchase_date', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $monthlySales = Sale::query()
            ->selectRaw("DATE_FORMAT(sale_date, '%Y-%m') as month, SUM(grand_total) as total")
            ->where('sale_status', 'completed')
            ->where('sale_date', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $months = collect(range(0, 11))->map(function ($i) {
            return now()->subMonths(11 - $i)->format('Y-m');
        });

        $chartMonthly = $months->map(function ($m) use ($monthlyPurchases, $monthlySales) {
            $purchase = (float) ($monthlyPurchases[$m] ?? 0);
            $sale = (float) ($monthlySales[$m] ?? 0);
            return [
                'month' => $m,
                'label' => date('M Y', strtotime($m . '-01')),
                'purchases' => $purchase,
                'sales' => $sale,
                'profit' => $sale - $purchase,
            ];
        })->values();

        $warehouseDistribution = WarehouseStock::query()
            ->join('warehouses', 'warehouse_stocks.warehouse_id', '=', 'warehouses.id')
            ->selectRaw('warehouses.name as name, SUM(warehouse_stocks.quantity) as quantity, SUM(warehouse_stocks.stock_value) as value')
            ->groupBy('warehouses.id', 'warehouses.name')
            ->orderByDesc('quantity')
            ->get();

        $categoryInventory = WarehouseStock::query()
            ->join('products', 'warehouse_stocks.product_id', '=', 'products.id')
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->selectRaw("COALESCE(product_categories.name, 'Uncategorized') as name, SUM(warehouse_stocks.quantity) as quantity, SUM(warehouse_stocks.stock_value) as value")
            ->groupBy('name')
            ->orderByDesc('quantity')
            ->limit(10)
            ->get();

        $topSelling = SaleDetail::query()
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->where('sales.sale_status', 'completed')
            ->selectRaw('products.name as name, SUM(sale_details.quantity) as qty, SUM(sale_details.line_total) as amount')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        $topCustomers = Sale::query()
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->where('sales.sale_status', 'completed')
            ->selectRaw('customers.customer_name as name, COUNT(sales.id) as invoices, SUM(sales.grand_total) as amount')
            ->groupBy('customers.id', 'customers.customer_name')
            ->orderByDesc('amount')
            ->limit(10)
            ->get();

        $mostPurchased = DB::table('purchase_details')
            ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->join('products', 'purchase_details.product_id', '=', 'products.id')
            ->whereNull('purchases.deleted_at')
            ->where('purchases.purchase_status', '!=', 'cancelled')
            ->selectRaw('products.name as name, SUM(purchase_details.quantity) as qty, SUM(purchase_details.line_total) as amount')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        $damageTrend = DamagedStock::query()
            ->selectRaw("DATE_FORMAT(damage_date, '%Y-%m') as month, SUM(total_quantity) as qty, SUM(total_amount) as amount")
            ->where('damage_date', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $adjustmentTrend = StockAdjustment::query()
            ->selectRaw("DATE_FORMAT(adjustment_date, '%Y-%m') as month, SUM(total_quantity) as qty, SUM(total_amount) as amount")
            ->where('adjustment_date', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $uniqueSuppliers = (int) Purchase::query()
            ->whereNotNull('supplier_name')
            ->where('supplier_name', '!=', '')
            ->selectRaw('COUNT(DISTINCT supplier_name) as c')
            ->value('c');

        return Inertia::render('Dashboard', [
            'kpis' => [
                'total_products' => Product::count(),
                'total_warehouses' => Warehouse::count(),
                'total_customers' => Customer::count(),
                'total_suppliers' => $uniqueSuppliers,
                'total_purchases' => Purchase::count(),
                'total_sales' => Sale::count(),
                'total_purchase_returns' => PurchaseReturn::count(),
                'total_sales_returns' => SaleReturn::count(),
                'total_transfers' => StockTransfer::count(),
                'total_adjustments' => StockAdjustment::count(),
                'total_damaged' => DamagedStock::count(),
                'total_inventory_qty' => $totalInventoryQty,
                'inventory_value' => $inventoryValue,
                'purchase_amount' => $purchaseAmount,
                'sales_amount' => $salesAmount,
                'purchase_expenses' => $purchaseExpenseTotal,
                'purchase_return_amount' => $purchaseReturnAmount,
                'sales_return_amount' => $saleReturnAmount,
                'purchase_today' => (float) Purchase::whereDate('purchase_date', today())->sum('grand_total'),
                'sales_today' => (float) Sale::where('sale_status', 'completed')->whereDate('sale_date', today())->sum('grand_total'),
                'stock_transfers_today' => StockTransfer::whereDate('transfer_date', today())->count(),
                'gross_profit' => $grossProfit,
                'net_profit' => $netProfit,
                'outstanding_balance' => $allOutstanding,
                'low_stock_count' => WarehouseStock::whereColumn('available_quantity', '<=', 'minimum_stock')->where('available_quantity', '>', 0)->count(),
                'out_of_stock_count' => WarehouseStock::where('available_quantity', '<=', 0)->count(),
            ],
            'charts' => [
                'monthly' => $chartMonthly,
                'warehouse_distribution' => $warehouseDistribution,
                'category_inventory' => $categoryInventory,
                'top_selling' => $topSelling,
                'top_customers' => $topCustomers,
                'most_purchased' => $mostPurchased,
                'damage_trend' => $damageTrend,
                'adjustment_trend' => $adjustmentTrend,
            ],
            'tables' => [
                'recent_purchases' => Purchase::with('warehouse')->latest('purchase_date')->limit(8)->get(),
                'recent_sales' => Sale::with(['customer', 'warehouse'])->latest('sale_date')->limit(8)->get(),
                'recent_transfers' => StockTransfer::with(['fromWarehouse', 'toWarehouse'])->latest('transfer_date')->limit(8)->get(),
                'recent_damaged' => DamagedStock::with('warehouse')->latest('damage_date')->limit(8)->get(),
                'recent_adjustments' => StockAdjustment::with('warehouse')->latest('adjustment_date')->limit(8)->get(),
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
                'outstanding_customers' => $outstandingCustomers,
                'recent_activities' => $this->recentActivities(),
            ],
        ]);
    }

    protected function recentActivities(): array
    {
        $items = collect();

        foreach (Purchase::latest()->limit(5)->get() as $p) {
            $items->push(['type' => 'Purchase', 'ref' => $p->purchase_no, 'date' => $p->purchase_date?->format('Y-m-d'), 'amount' => $p->grand_total, 'id' => $p->id]);
        }
        foreach (Sale::latest()->limit(5)->get() as $s) {
            $items->push(['type' => 'Sale', 'ref' => $s->invoice_no, 'date' => $s->sale_date?->format('Y-m-d'), 'amount' => $s->grand_total, 'id' => $s->id]);
        }
        foreach (SaleReturn::latest()->limit(3)->get() as $sr) {
            $items->push(['type' => 'Sale Return', 'ref' => $sr->reference_no, 'date' => $sr->return_date?->format('Y-m-d'), 'amount' => $sr->total_amount, 'id' => $sr->id]);
        }
        foreach (StockTransfer::latest()->limit(3)->get() as $t) {
            $items->push(['type' => 'Transfer', 'ref' => $t->reference_no, 'date' => $t->transfer_date?->format('Y-m-d'), 'amount' => $t->total_amount, 'id' => $t->id]);
        }
        foreach (DamagedStock::latest()->limit(3)->get() as $d) {
            $items->push(['type' => 'Damage', 'ref' => $d->reference_no, 'date' => $d->damage_date?->format('Y-m-d'), 'amount' => $d->total_amount, 'id' => $d->id]);
        }
        foreach (StockAdjustment::latest()->limit(3)->get() as $a) {
            $items->push(['type' => 'Adjustment', 'ref' => $a->reference_no, 'date' => $a->adjustment_date?->format('Y-m-d'), 'amount' => $a->total_amount, 'id' => $a->id]);
        }
        foreach (OpeningStock::latest()->limit(3)->get() as $o) {
            $items->push(['type' => 'Opening', 'ref' => $o->reference_no, 'date' => $o->opening_date?->format('Y-m-d'), 'amount' => $o->total_amount, 'id' => $o->id]);
        }

        return $items->sortByDesc('date')->take(15)->values()->all();
    }
}
