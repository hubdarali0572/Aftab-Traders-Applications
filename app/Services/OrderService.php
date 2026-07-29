<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderReturn;
use Illuminate\Database\Eloquent\Builder;

class OrderService
{
    public function applyOrderFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($inner) use ($search) {
                    $inner->where('order_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('customer_name', 'like', "%{$search}%"));
                });
            })
            ->when(! empty($filters['order_status']), fn ($q) => $q->where('order_status', $filters['order_status']));
    }

    public function applyReturnFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($inner) use ($search) {
                    $inner->where('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('order', fn ($o) => $o->where('order_no', 'like', "%{$search}%"))
                        ->orWhereHas('customer', fn ($c) => $c->where('customer_name', 'like', "%{$search}%"));
                });
            })
            ->when(! empty($filters['return_status']), fn ($q) => $q->where('return_status', $filters['return_status']));
    }

    public function generateOrderNo(): string
    {
        $next = (int) Order::withTrashed()->max('id') + 1;

        return 'ORD-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function generateReturnReferenceNo(): string
    {
        $next = (int) OrderReturn::withTrashed()->max('id') + 1;

        return 'OR-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function ordersDashboardSummary(array $filters = []): array
    {
        $baseQuery = Order::query();
        $this->applyOrderFilters($baseQuery, $filters);

        $completedQuery = (clone $baseQuery)->where('order_status', 'completed');
        $completedIds = (clone $completedQuery)->pluck('id');

        $returnQuery = OrderReturn::query();
        $this->applyReturnFilters($returnQuery, $filters);

        return [
            'total_orders' => (clone $baseQuery)->count(),
            'total_order_amount' => round((float) (clone $completedQuery)->sum('grand_total'), 2),
            'total_order_returns' => (clone $returnQuery)->count(),
            'total_return_amount' => round((float) (clone $returnQuery)->where('return_status', 'completed')->sum('total_amount'), 2),
            'total_products_ordered' => round((float) OrderDetail::whereIn('order_id', $completedIds)->sum('quantity'), 2),
            'total_outstanding' => round((float) (clone $completedQuery)->whereNotNull('customer_id')->sum('due_amount'), 2),
        ];
    }

    public function returnsDashboardSummary(array $filters = []): array
    {
        $returnQuery = OrderReturn::query();
        $this->applyReturnFilters($returnQuery, $filters);

        return [
            'total_returns' => (clone $returnQuery)->count(),
            'total_return_amount' => round((float) (clone $returnQuery)->sum('total_amount'), 2),
            'total_return_quantity' => round((float) (clone $returnQuery)->sum('total_quantity'), 2),
        ];
    }
}
