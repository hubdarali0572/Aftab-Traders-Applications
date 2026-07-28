<?php

namespace App\Http\Controllers\Reports;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\ExpenseHead;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Reports\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

trait BuildsReportResponses
{
    protected function filterOptions(): array
    {
        return [
            'warehouses' => Warehouse::orderBy('name')->get(['id', 'name']),
            'customers' => Customer::orderBy('customer_name')->get(['id', 'customer_name', 'company_name', 'customer_code']),
            'products' => Product::orderBy('name')->limit(500)->get(['id', 'name', 'sku']),
            'categories' => ProductCategory::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'expenseHeads' => ExpenseHead::orderBy('name')->get(['id', 'name', 'head_code']),
        ];
    }

    protected function paginateCollection(Collection $items, Request $request, int $perPage = 25): LengthAwarePaginator
    {
        $page = max(1, (int) $request->input('page', 1));
        $slice = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    protected function maybeCsv(Request $request, ReportExportService $export, string $filename, array $headers, iterable $rows)
    {
        if ($request->input('export') === 'excel' || $request->input('export') === 'csv') {
            return $export->csv($filename, $headers, $rows);
        }

        return null;
    }
}
