<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Warehouse;
use App\Services\Reports\ReportExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ExpenseHistoryService
{
    public function __construct(
        protected ExpenseService $expenses,
        protected ReportExportService $export
    ) {
    }

    public function filterKeys(): array
    {
        return [
            'search',
            'warehouse_id',
            'status',
            'payment_method',
            'date_from',
            'date_to',
            'sort',
            'direction',
        ];
    }

    public function filters(Request $request): array
    {
        return $request->only($this->filterKeys());
    }

    public function historyQuery(Request $request): Builder
    {
        return $this->expenses->applyFilters(Expense::query(), $request);
    }

    public function dashboardSummary(Request $request): array
    {
        $base = $this->historyQuery($request);
        $financial = (clone $base)->whereIn('expenses.status', ExpenseService::FINANCIAL_STATUSES);

        $totalFinancialAmount = round((float) (clone $financial)->sum('expenses.amount'), 2);
        $totalCount = (clone $base)->count();
        $financialCount = (clone $financial)->count();

        $todayFinancial = round((float) (clone $financial)
            ->whereDate('expenses.expense_date', today())
            ->sum('expenses.amount'), 2);

        $monthFinancial = round((float) (clone $financial)
            ->whereYear('expenses.expense_date', now()->year)
            ->whereMonth('expenses.expense_date', now()->month)
            ->sum('expenses.amount'), 2);

        $draftAmount = round((float) (clone $base)->where('expenses.status', 'draft')->sum('expenses.amount'), 2);
        $paidAmount = round((float) (clone $base)->where('expenses.status', 'paid')->sum('expenses.amount'), 2);
        $cancelledCount = (clone $base)->where('expenses.status', 'cancelled')->count();

        $byPayment = (clone $financial)
            ->selectRaw('expenses.payment_method as payment_method, COUNT(*) as count, COALESCE(SUM(expenses.amount), 0) as amount')
            ->groupBy('expenses.payment_method')
            ->orderByDesc('amount')
            ->get()
            ->map(fn ($row) => [
                'payment_method' => $row->payment_method,
                'count' => (int) $row->count,
                'amount' => round((float) $row->amount, 2),
            ]);

        $monthlyTrend = $this->monthlyTrend($request);

        return [
            'total_expenses' => $totalCount,
            'financial_count' => $financialCount,
            'total_expense_amount' => $totalFinancialAmount,
            'expenses_today' => $todayFinancial,
            'expenses_this_month' => $monthFinancial,
            'draft_amount' => $draftAmount,
            'paid_amount' => $paidAmount,
            'cancelled_count' => $cancelledCount,
            'by_payment_method' => $byPayment,
            'monthly_trend' => $monthlyTrend,
        ];
    }

    public function monthlyTrend(Request $request): Collection
    {
        $query = (clone $this->historyQuery($request))
            ->whereIn('expenses.status', ExpenseService::FINANCIAL_STATUSES)
            ->where('expenses.expense_date', '>=', now()->subMonths(11)->startOfMonth());

        return $query
            ->selectRaw("DATE_FORMAT(expenses.expense_date, '%Y-%m') as month, COUNT(*) as count, COALESCE(SUM(expenses.amount), 0) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'month' => $row->month,
                'label' => date('M Y', strtotime($row->month . '-01')),
                'count' => (int) $row->count,
                'total' => round((float) $row->total, 2),
            ]);
    }

    public function paginateExpenses(Request $request, int $perPage = 10)
    {
        $sort = $request->input('sort', 'expense_date');
        $direction = $request->input('direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['expense_date', 'expense_no', 'amount', 'status', 'payment_method', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'expense_date';
        }

        $sortColumn = str_contains($sort, '.') ? $sort : "expenses.{$sort}";

        return $this->historyQuery($request)
            ->with([
                'warehouse:id,name',
                'user:id,name',
            ])
            ->orderBy($sortColumn, $direction)
            ->when($sort !== 'id', fn (Builder $q) => $q->orderByDesc('expenses.id'))
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Expense $expense) => $this->transformExpense($expense));
    }

    protected function transformExpense(Expense $expense): array
    {
        return [
            'id' => $expense->id,
            'expense_no' => $expense->expense_no,
            'expense_date' => $expense->expense_date?->format('Y-m-d'),
            'expense_name' => $expense->expense_name,
            'warehouse_name' => $expense->warehouse?->name,
            'employee_name' => $expense->employee_name,
            'payee_name' => $expense->payee_name,
            'amount' => round((float) $expense->amount, 2),
            'payment_method' => $expense->payment_method,
            'reference_no' => $expense->reference_no,
            'invoice_no' => $expense->invoice_no,
            'status' => $expense->status,
            'description' => $expense->description,
            'recorded_by' => $expense->user?->name,
            'created_at' => $expense->created_at?->toIso8601String(),
        ];
    }

    public function exportRows(Request $request): Collection
    {
        $sort = $request->input('sort', 'expense_date');
        $direction = $request->input('direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['expense_date', 'expense_no', 'amount', 'status', 'payment_method', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'expense_date';
        }

        $sortColumn = str_contains($sort, '.') ? $sort : "expenses.{$sort}";

        return $this->historyQuery($request)
            ->with(['warehouse', 'user'])
            ->orderBy($sortColumn, $direction)
            ->when($sort !== 'id', fn (Builder $q) => $q->orderByDesc('expenses.id'))
            ->get()
            ->map(fn (Expense $expense) => [
                $expense->expense_date?->format('Y-m-d'),
                $expense->expense_no,
                $expense->expense_name,
                $expense->warehouse?->name ?? 'Company-wide',
                $expense->payee_name,
                $expense->employee_name,
                number_format((float) $expense->amount, 2, '.', ''),
                $expense->payment_method,
                $expense->reference_no,
                $expense->invoice_no,
                $expense->status,
                $expense->user?->name,
                $expense->description ?: $expense->remarks,
            ]);
    }

    public function filterOptions(): array
    {
        return [
            'warehouses' => Warehouse::orderBy('name')->get(['id', 'name']),
        ];
    }
}
