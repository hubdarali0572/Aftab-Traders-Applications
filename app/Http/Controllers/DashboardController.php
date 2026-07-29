<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\DamagedStock;
use App\Models\Expense;
use App\Models\Order;
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
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalInventoryQty = (float) Stock::sum('quantity');
        $inventoryValue = (float) Stock::selectRaw('SUM(quantity * average_cost) as val')->value('val');

        $purchaseAmount = (float) Purchase::where('purchase_status', '!=', 'cancelled')->sum('grand_total');
        $salesAmount = (float) Sale::where('sale_status', 'completed')->sum('grand_total');
        $orderStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        $orderStatusStats = Order::query()
            ->selectRaw('order_status, COUNT(*) as count, COALESCE(SUM(grand_total), 0) as amount')
            ->groupBy('order_status')
            ->get()
            ->keyBy('order_status');

        $ordersByStatus = collect($orderStatuses)->mapWithKeys(function ($status) use ($orderStatusStats) {
            $row = $orderStatusStats->get($status);

            return [
                $status => [
                    'count' => (int) ($row->count ?? 0),
                    'amount' => (float) ($row->amount ?? 0),
                ],
            ];
        })->all();

        // Include any other statuses (e.g. processing) in totals only
        $allOrdersCount = (int) $orderStatusStats->sum('count');
        $allOrdersAmount = (float) $orderStatusStats->sum('amount');
        $totalOrdersCount = (int) $orderStatusStats
            ->reject(fn ($row, $status) => $status === 'cancelled')
            ->sum('count');
        $ordersAmount = (float) $orderStatusStats
            ->reject(fn ($row, $status) => $status === 'cancelled')
            ->sum('amount');

        $expenseStatuses = ['draft', 'approved', 'paid', 'cancelled'];
        $expenseStatusStats = Expense::query()
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(amount), 0) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $expensesByStatus = collect($expenseStatuses)->mapWithKeys(function ($status) use ($expenseStatusStats) {
            $row = $expenseStatusStats->get($status);

            return [
                $status => [
                    'count' => (int) ($row->count ?? 0),
                    'amount' => (float) ($row->amount ?? 0),
                ],
            ];
        })->all();

        $allExpensesCount = (int) $expenseStatusStats->sum('count');
        $allExpensesAmount = (float) $expenseStatusStats->sum('amount');
        $activeExpensesCount = (int) $expenseStatusStats
            ->reject(fn ($row, $status) => $status === 'cancelled')
            ->sum('count');
        $activeExpensesAmount = (float) $expenseStatusStats
            ->reject(fn ($row, $status) => $status === 'cancelled')
            ->sum('amount');

        $purchaseExpenseTotal = (float) PurchaseExpense::sum('amount');
        $purchaseReturnAmount = (float) PurchaseReturn::sum('total_amount');
        $saleReturnAmount = (float) SaleReturn::sum('total_amount');

        // COGS approximation: sum of sale line quantities × warehouse average cost
        $cogs = (float) SaleDetail::query()
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('stocks', function ($join) {
                $join->on('stocks.product_id', '=', 'sale_details.product_id')
                    ->on('stocks.warehouse_id', '=', 'sales.warehouse_id');
            })
            ->where('sales.sale_status', 'completed')
            ->selectRaw('COALESCE(SUM(sale_details.quantity * stocks.average_cost), 0) as cogs')
            ->value('cogs');

        $grossProfit = $salesAmount - $cogs;
        $netProfit = $grossProfit - $purchaseExpenseTotal;

        $lowStock = Stock::with(['product', 'warehouse'])
            ->whereColumn('quantity', '<=', 'minimum_stock')
            ->where('quantity', '>', 0)
            ->orderBy('quantity')
            ->limit(10)
            ->get();

        $outOfStock = Stock::with(['product', 'warehouse'])
            ->where('quantity', '<=', 0)
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

        $warehouseDistribution = Stock::query()
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->selectRaw('warehouses.name as name, SUM(stocks.quantity) as quantity, SUM(stocks.quantity * stocks.average_cost) as value')
            ->groupBy('warehouses.id', 'warehouses.name')
            ->orderByDesc('quantity')
            ->get();

        $categoryInventory = Stock::query()
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->selectRaw("COALESCE(product_categories.name, 'Uncategorized') as name, SUM(stocks.quantity) as quantity, SUM(stocks.quantity * stocks.average_cost) as value")
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
                'total_orders' => $totalOrdersCount,
                'orders_amount' => $ordersAmount,
                'all_orders_count' => $allOrdersCount,
                'all_orders_amount' => $allOrdersAmount,
                'orders_by_status' => $ordersByStatus,
                'all_expenses_count' => $allExpensesCount,
                'all_expenses_amount' => $allExpensesAmount,
                'total_expenses' => $activeExpensesCount,
                'expenses_amount' => $activeExpensesAmount,
                'expenses_by_status' => $expensesByStatus,
                'expenses_today' => (float) Expense::where('status', '!=', 'cancelled')->whereDate('expense_date', today())->sum('amount'),
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
                'low_stock_count' => Stock::whereColumn('quantity', '<=', 'minimum_stock')->where('quantity', '>', 0)->count(),
                'out_of_stock_count' => Stock::where('quantity', '<=', 0)->count(),
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
                'recent_transfers' => StockTransfer::with(['fromWarehouse', 'toWarehouse', 'product'])->latest('transfer_date')->limit(8)->get(),
                'recent_damaged' => DamagedStock::with('warehouse')->latest('damage_date')->limit(8)->get(),
                'recent_adjustments' => StockAdjustment::with('warehouse')->latest('adjustment_date')->limit(8)->get(),
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
                'outstanding_customers' => $outstandingCustomers,
            ],
        ]);
    }
}
