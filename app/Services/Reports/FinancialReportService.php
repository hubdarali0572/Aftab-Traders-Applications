<?php

namespace App\Services\Reports;

use App\Models\Expense;
use App\Models\Purchase;
use App\Models\PurchaseExpense;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    public function __construct(protected ReportExportService $export)
    {
    }

    public function expenses(Request $request): array
    {
        $query = Expense::query()
            ->with(['expenseHead', 'warehouse', 'user'])
            ->where('expenses.status', '!=', 'cancelled');

        $this->export->applyDateRange($query, $request, 'expense_date');

        if ($request->filled('expense_head_id')) {
            $query->where('expenses.expense_head_id', $request->expense_head_id);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('expenses.warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('payment_method')) {
            $query->where('expenses.payment_method', $request->payment_method);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('expense_no', 'like', "%{$search}%")
                    ->orWhere('payee_name', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%")
                    ->orWhereHas('expenseHead', fn ($h) => $h->where('name', 'like', "%{$search}%"));
            });
        }

        $rows = $query->latest('expense_date')->get()->map(fn ($e) => [
            'expense_date' => $e->expense_date?->format('Y-m-d'),
            'expense_no' => $e->expense_no,
            'expense_head' => $e->expenseHead?->name,
            'expense_category' => $e->expenseHead?->head_code,
            'amount' => (float) $e->amount,
            'paid_to' => $e->payee_name,
            'payment_method' => $e->payment_method,
            'remarks' => $e->remarks ?: $e->description,
            'recorded_by' => $e->user?->name,
            'warehouse' => $e->warehouse?->name,
            'status' => $e->status,
        ])->values();

        $byCategory = $rows->groupBy('expense_head')->map(fn ($items, $head) => [
            'name' => $head ?: 'Uncategorized',
            'amount' => (float) $items->sum('amount'),
            'count' => $items->count(),
        ])->values();

        $monthly = Expense::query()
            ->where('expenses.status', '!=', 'cancelled')
            ->when($request->filled('expense_head_id'), fn ($q) => $q->where('expense_head_id', $request->expense_head_id));
        $this->export->applyDateRange($monthly, $request, 'expense_date');

        $trend = $monthly
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($r) => [
                'month' => $r->month,
                'label' => date('M Y', strtotime($r->month . '-01')),
                'total' => (float) $r->total,
            ]);

        $summary = [
            'total_expenses' => (float) $rows->sum('amount'),
            'expense_count' => $rows->count(),
            'by_category' => $byCategory,
            'monthly_trend' => $trend,
        ];

        return ['rows' => $rows, 'summary' => $summary];
    }

    public function profitAndLoss(Request $request): array
    {
        $salesQuery = Sale::query()->where('sale_status', 'completed');
        $this->export->applyDateRange($salesQuery, $request, 'sale_date');
        if ($request->filled('warehouse_id')) {
            $salesQuery->where('warehouse_id', $request->warehouse_id);
        }

        $saleReturnsQuery = SaleReturn::query();
        $this->export->applyDateRange($saleReturnsQuery, $request, 'return_date');
        if ($request->filled('warehouse_id')) {
            $saleReturnsQuery->where('warehouse_id', $request->warehouse_id);
        }

        $purchasesQuery = Purchase::query()->where('purchase_status', '!=', 'cancelled');
        $this->export->applyDateRange($purchasesQuery, $request, 'purchase_date');
        if ($request->filled('warehouse_id')) {
            $purchasesQuery->where('warehouse_id', $request->warehouse_id);
        }

        $purchaseReturnsQuery = PurchaseReturn::query();
        $this->export->applyDateRange($purchaseReturnsQuery, $request, 'return_date');
        if ($request->filled('warehouse_id')) {
            $purchaseReturnsQuery->where('warehouse_id', $request->warehouse_id);
        }

        $expensesQuery = Expense::query()->where('expenses.status', '!=', 'cancelled');
        $this->export->applyDateRange($expensesQuery, $request, 'expense_date');
        if ($request->filled('warehouse_id')) {
            $expensesQuery->where('expenses.warehouse_id', $request->warehouse_id);
        }

        $salesRevenue = (float) (clone $salesQuery)->sum('grand_total');
        $salesReturns = (float) (clone $saleReturnsQuery)->sum('total_amount');
        $netSales = $salesRevenue - $salesReturns;

        $purchases = (float) (clone $purchasesQuery)->sum('grand_total');
        $purchaseExpenses = (float) PurchaseExpense::query()
            ->whereIn('purchase_id', (clone $purchasesQuery)->select('id'))
            ->sum('amount');
        $purchaseReturns = (float) (clone $purchaseReturnsQuery)->sum('total_amount');

        $stockQuery = WarehouseStock::query();
        if ($request->filled('warehouse_id')) {
            $stockQuery->where('warehouse_id', $request->warehouse_id);
        }
        $closingStock = (float) $stockQuery->sum('stock_value');

        // Opening stock approximation: closing + COGS outflow - inflow within period is complex;
        // use opening stock documents total as opening, else derive from closing + sold cost - purchased.
        $openingStock = (float) DB::table('opening_stocks')
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('opening_date', '<', $request->date_from))
            ->whereNull('deleted_at')
            ->sum('total_amount');

        if ($openingStock <= 0) {
            // Fallback: treat current stock value as closing and estimate opening from purchases/sales period
            $openingStock = max($closingStock + ($salesRevenue * 0) - $purchases + $purchaseReturns, 0);
            // Prefer using ledger unit costs for sold qty is heavy; keep opening as prior opening docs or 0
            $openingStock = (float) DB::table('opening_stocks')
                ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
                ->whereNull('deleted_at')
                ->sum('total_amount');
        }

        $cogs = $openingStock + $purchases + $purchaseExpenses - $purchaseReturns - $closingStock;
        if ($cogs < 0) {
            $cogs = max($purchases + $purchaseExpenses - $purchaseReturns, 0);
        }

        $grossProfit = $netSales - $cogs;
        $operatingExpenses = (float) (clone $expensesQuery)->sum('amount');
        $netProfit = $grossProfit - $operatingExpenses;

        $expenseByHead = (clone $expensesQuery)
            ->join('expense_heads', 'expenses.expense_head_id', '=', 'expense_heads.id')
            ->where('expenses.status', '!=', 'cancelled')
            ->selectRaw('expense_heads.name as name, SUM(expenses.amount) as amount')
            ->groupBy('expense_heads.id', 'expense_heads.name')
            ->orderByDesc('amount')
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'amount' => (float) $r->amount]);

        $months = collect(range(0, 11))->map(fn ($i) => now()->subMonths(11 - $i)->format('Y-m'));

        $monthlySales = Sale::query()
            ->where('sale_status', 'completed')
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->where('sale_date', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(sale_date, '%Y-%m') as month, SUM(grand_total) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $monthlyExpenses = Expense::query()
            ->where('expenses.status', '!=', 'cancelled')
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->where('expense_date', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as month, SUM(amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $monthlyTrend = $months->map(function ($m) use ($monthlySales, $monthlyExpenses) {
            $rev = (float) ($monthlySales[$m] ?? 0);
            $exp = (float) ($monthlyExpenses[$m] ?? 0);

            return [
                'month' => $m,
                'label' => date('M Y', strtotime($m . '-01')),
                'revenue' => $rev,
                'expenses' => $exp,
                'profit' => $rev - $exp,
            ];
        })->values();

        return [
            'summary' => [
                'sales_revenue' => $salesRevenue,
                'sales_returns' => $salesReturns,
                'net_sales' => $netSales,
                'opening_stock' => $openingStock,
                'purchases' => $purchases,
                'purchase_expenses' => $purchaseExpenses,
                'purchase_returns' => $purchaseReturns,
                'closing_stock' => $closingStock,
                'cogs' => $cogs,
                'gross_profit' => $grossProfit,
                'operating_expenses' => $operatingExpenses,
                'net_profit' => $netProfit,
                'total_revenue' => $salesRevenue,
            ],
            'expense_distribution' => $expenseByHead,
            'monthly_trend' => $monthlyTrend,
        ];
    }
}
