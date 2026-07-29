<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Expense;
use App\Services\ExpenseService;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Stock;
use App\Models\Warehouse;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalInventoryQty = (float) Stock::sum('quantity');
        $inventoryValue = (float) Stock::selectRaw('SUM(quantity * average_cost) as val')->value('val');

        $purchaseAmount = (float) Purchase::where('purchase_status', '!=', 'cancelled')->sum('grand_total');
        $salesAmount = (float) Sale::where('sale_status', 'completed')->sum('grand_total');

        $operatingExpenseTotal = (float) Expense::query()
            ->whereIn('status', ExpenseService::FINANCIAL_STATUSES)
            ->sum('amount');

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
        $netProfit = $grossProfit - $operatingExpenseTotal;

        $lowStock = Stock::with(['product', 'warehouse'])
            ->whereColumn('quantity', '<=', 'minimum_stock')
            ->where('quantity', '>', 0)
            ->orderBy('quantity')
            ->limit(6)
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
            ->take(5)
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

        $monthlyExpenses = Expense::query()
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as month, SUM(amount) as total")
            ->whereIn('status', ExpenseService::FINANCIAL_STATUSES)
            ->where('expense_date', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $months = collect(range(0, 11))->map(function ($i) {
            return now()->subMonths(11 - $i)->format('Y-m');
        });

        $chartMonthly = $months->map(function ($m) use ($monthlyPurchases, $monthlySales, $monthlyExpenses) {
            $purchase = (float) ($monthlyPurchases[$m] ?? 0);
            $sale = (float) ($monthlySales[$m] ?? 0);
            $expense = (float) ($monthlyExpenses[$m] ?? 0);

            return [
                'month' => $m,
                'label' => date('M Y', strtotime($m . '-01')),
                'purchases' => $purchase,
                'sales' => $sale,
                'expenses' => $expense,
                'profit' => $sale - $purchase - $expense,
            ];
        })->values();

        $warehouseDistribution = Stock::query()
            ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
            ->selectRaw('warehouses.name as name, SUM(stocks.quantity) as quantity, SUM(stocks.quantity * stocks.average_cost) as value')
            ->groupBy('warehouses.id', 'warehouses.name')
            ->orderByDesc('quantity')
            ->get();

        $topSelling = SaleDetail::query()
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->where('sales.sale_status', 'completed')
            ->selectRaw('products.name as name, SUM(sale_details.quantity) as qty, SUM(sale_details.line_total) as amount')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty')
            ->limit(6)
            ->get();

        $topCustomers = Sale::query()
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->where('sales.sale_status', 'completed')
            ->selectRaw('customers.customer_name as name, COUNT(sales.id) as invoices, SUM(sales.grand_total) as amount')
            ->groupBy('customers.id', 'customers.customer_name')
            ->orderByDesc('amount')
            ->limit(6)
            ->get();

        return Inertia::render('Dashboard', [
            'kpis' => [
                'total_customers' => Customer::count(),
                'total_products' => Product::count(),
                'total_warehouses' => Warehouse::count(),
                'inventory_value' => $inventoryValue,
                'total_inventory_qty' => $totalInventoryQty,
                'purchase_amount' => $purchaseAmount,
                'sales_amount' => $salesAmount,
                'purchase_today' => (float) Purchase::whereDate('purchase_date', today())->where('purchase_status', '!=', 'cancelled')->sum('grand_total'),
                'sales_today' => (float) Sale::where('sale_status', 'completed')->whereDate('sale_date', today())->sum('grand_total'),
                'expenses_today' => (float) Expense::query()
                    ->whereIn('status', ExpenseService::FINANCIAL_STATUSES)
                    ->whereDate('expense_date', today())
                    ->sum('amount'),
                'operating_expenses' => $operatingExpenseTotal,
                'gross_profit' => $grossProfit,
                'net_profit' => $netProfit,
                'outstanding_balance' => $allOutstanding,
                'low_stock_count' => Stock::whereColumn('quantity', '<=', 'minimum_stock')->where('quantity', '>', 0)->count(),
                'out_of_stock_count' => Stock::where('quantity', '<=', 0)->count(),
            ],
            'charts' => [
                'monthly' => $chartMonthly,
                'warehouse_distribution' => $warehouseDistribution,
                'top_selling' => $topSelling,
                'top_customers' => $topCustomers,
            ],
            'tables' => [
                'recent_purchases' => Purchase::with('warehouse')->latest('purchase_date')->limit(5)->get(),
                'recent_sales' => Sale::with(['customer', 'warehouse'])->latest('sale_date')->limit(5)->get(),
                'low_stock' => $lowStock,
                'outstanding_customers' => $outstandingCustomers,
            ],
        ]);
    }
}
