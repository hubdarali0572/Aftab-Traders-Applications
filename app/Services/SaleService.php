<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleReturn;
use Illuminate\Database\Eloquent\Builder;

class SaleService
{
    public function applySaleFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($inner) use ($search) {
                    $inner->where('invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('customer_name', 'like', "%{$search}%"));
                });
            })
            ->when(! empty($filters['sale_status']), fn ($q) => $q->where('sale_status', $filters['sale_status']));
    }

    public function applyReturnFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($inner) use ($search) {
                    $inner->where('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('sale', fn ($s) => $s->where('invoice_no', 'like', "%{$search}%"))
                        ->orWhereHas('customer', fn ($c) => $c->where('customer_name', 'like', "%{$search}%"));
                });
            });
    }

    public function generateInvoiceNo(): string
    {
        $next = (int) Sale::withTrashed()->max('id') + 1;

        return 'INV-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function generateReturnReferenceNo(): string
    {
        $next = (int) SaleReturn::withTrashed()->max('id') + 1;

        return 'SR-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function salesDashboardSummary(array $filters = []): array
    {
        $baseQuery = Sale::query();
        $this->applySaleFilters($baseQuery, $filters);
        $saleIds = (clone $baseQuery)->pluck('id');

        $completedQuery = (clone $baseQuery)->where('sale_status', 'completed');

        $returnQuery = SaleReturn::query();
        $this->applyReturnFilters($returnQuery, $filters);

        $totalOutstanding = (float) (clone $completedQuery)
            ->whereNotNull('customer_id')
            ->sum('due_amount');

        return [
            'total_sales' => $saleIds->count(),
            'total_sales_amount' => round((float) (clone $completedQuery)->sum('grand_total'), 2),
            'total_sales_returns' => (clone $returnQuery)->count(),
            'total_return_amount' => round((float) (clone $returnQuery)->sum('total_amount'), 2),
            'total_products_sold' => round((float) SaleDetail::whereIn('sale_id', $completedQuery->pluck('id'))->sum('quantity'), 2),
            'total_outstanding' => round($totalOutstanding, 2),
        ];
    }

    public function returnsDashboardSummary(array $filters = []): array
    {
        $returnQuery = SaleReturn::query();
        $this->applyReturnFilters($returnQuery, $filters);

        return [
            'total_returns' => (clone $returnQuery)->count(),
            'total_return_amount' => round((float) (clone $returnQuery)->sum('total_amount'), 2),
            'total_return_quantity' => round((float) (clone $returnQuery)->sum('total_quantity'), 2),
            'total_sales_returns' => (clone $returnQuery)->count(),
        ];
    }
}
