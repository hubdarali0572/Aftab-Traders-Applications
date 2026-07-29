<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ExpenseService
{
    /** Statuses that count toward financial totals (P&L, dashboard, reports). */
    public const FINANCIAL_STATUSES = ['approved', 'paid'];

    public function filterKeys(): array
    {
        return [
            'search',
            'warehouse_id',
            'status',
            'payment_method',
            'date_from',
            'date_to',
        ];
    }

    public function filters(Request $request): array
    {
        return $request->only($this->filterKeys());
    }

    public function applyFinancialScope(Builder $query): Builder
    {
        return $query->whereIn('expenses.status', self::FINANCIAL_STATUSES);
    }

    public function applyFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('expenses.expense_no', 'like', "%{$search}%")
                    ->orWhere('expenses.expense_name', 'like', "%{$search}%")
                    ->orWhere('expenses.payee_name', 'like', "%{$search}%")
                    ->orWhere('expenses.employee_name', 'like', "%{$search}%")
                    ->orWhere('expenses.reference_no', 'like', "%{$search}%")
                    ->orWhere('expenses.invoice_no', 'like', "%{$search}%")
                    ->orWhere('expenses.description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('warehouse_id')) {
            $query->where('expenses.warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('status')) {
            $query->where('expenses.status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('expenses.payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expenses.expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expenses.expense_date', '<=', $request->date_to);
        }

        return $query;
    }

    public function baseQuery(Request $request): Builder
    {
        return $this->applyFilters(Expense::query(), $request);
    }

    public function paginate(Request $request, int $perPage = 15)
    {
        return $this->baseQuery($request)
            ->with(['warehouse', 'user'])
            ->latest('expense_date')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function listSummary(Request $request): array
    {
        $base = $this->baseQuery($request);
        $financial = (clone $base)->whereIn('expenses.status', self::FINANCIAL_STATUSES);

        $statusStats = (clone $base)
            ->selectRaw('expenses.status as status, COUNT(*) as count, COALESCE(SUM(expenses.amount), 0) as amount')
            ->groupBy('expenses.status')
            ->get()
            ->keyBy('status');

        $statuses = ['draft', 'approved', 'paid', 'cancelled'];
        $byStatus = collect($statuses)->mapWithKeys(function ($status) use ($statusStats) {
            $row = $statusStats->get($status);

            return [
                $status => [
                    'count' => (int) ($row->count ?? 0),
                    'amount' => round((float) ($row->amount ?? 0), 2),
                ],
            ];
        })->all();

        return [
            'total_count' => (clone $base)->count(),
            'financial_count' => (clone $financial)->count(),
            'financial_amount' => round((float) (clone $financial)->sum('expenses.amount'), 2),
            'draft_amount' => round((float) (clone $base)->where('expenses.status', 'draft')->sum('expenses.amount'), 2),
            'paid_amount' => round((float) (clone $base)->where('expenses.status', 'paid')->sum('expenses.amount'), 2),
            'today_amount' => round((float) (clone $financial)->whereDate('expenses.expense_date', today())->sum('expenses.amount'), 2),
            'month_amount' => round((float) (clone $financial)
                ->whereYear('expenses.expense_date', now()->year)
                ->whereMonth('expenses.expense_date', now()->month)
                ->sum('expenses.amount'), 2),
            'by_status' => $byStatus,
        ];
    }

    public function statusBreakdown(): Collection
    {
        return Expense::query()
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(amount), 0) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');
    }

    public function financialTotal(?Request $request = null): float
    {
        $query = Expense::query();
        $this->applyFinancialScope($query);

        if ($request) {
            $this->applyFilters($query, $request);
        }

        return round((float) $query->sum('amount'), 2);
    }

    public function generateExpenseNo(): string
    {
        $prefix = 'EXP-' . now()->format('Ymd') . '-';
        $latest = Expense::withTrashed()
            ->where('expense_no', 'like', $prefix . '%')
            ->orderByDesc('expense_no')
            ->value('expense_no');

        $sequence = 1;
        if ($latest && preg_match('/(\d+)$/', $latest, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
