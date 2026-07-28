<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    public function csv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        $filename = str_ends_with(strtolower($filename), '.csv') ? $filename : $filename . '.csv';

        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                $values = $row instanceof Collection || is_object($row)
                    ? (method_exists($row, 'toArray') ? array_values($row->toArray()) : array_values((array) $row))
                    : array_values($row);
                fputcsv($handle, $values);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function filters(Request $request): array
    {
        return [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'warehouse_id' => $request->input('warehouse_id'),
            'customer_id' => $request->input('customer_id'),
            'product_id' => $request->input('product_id'),
            'category_id' => $request->input('category_id'),
            'brand_id' => $request->input('brand_id'),
            'user_id' => $request->input('user_id'),
            'payment_status' => $request->input('payment_status'),
            'payment_method' => $request->input('payment_method'),
            'expense_head_id' => $request->input('expense_head_id'),
            'transaction_type' => $request->input('transaction_type'),
            'aging' => $request->input('aging'),
            'search' => $request->input('search'),
            'sort' => $request->input('sort'),
            'direction' => $request->input('direction', 'desc'),
            'export' => $request->input('export'),
            'print' => $request->boolean('print'),
        ];
    }

    public function applyDateRange($query, Request $request, string $column): void
    {
        if ($request->filled('date_from')) {
            $query->whereDate($column, '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate($column, '<=', $request->date_to);
        }
    }

    public function paymentStatus(?float $paid, ?float $due, ?float $grand): string
    {
        $paid = (float) $paid;
        $due = (float) $due;
        $grand = (float) $grand;

        if ($grand <= 0 && $paid <= 0) {
            return 'unpaid';
        }
        if ($due <= 0.009) {
            return 'paid';
        }
        if ($paid <= 0.009) {
            return 'unpaid';
        }

        return 'partial';
    }
}
