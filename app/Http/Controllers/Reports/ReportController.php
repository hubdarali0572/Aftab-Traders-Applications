<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        $featured = [
            [
                'name' => 'Profit & Loss',
                'route' => 'reports.financial.profit-loss',
                'desc' => 'Revenue, COGS, expenses and net profit with charts',
                'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
                'tone' => 'from-violet-500 to-purple-600',
                'badge' => 'Financial',
            ],
            [
                'name' => 'Daily Sales',
                'route' => 'reports.sales.daily',
                'desc' => 'Invoice-level sales for any date range',
                'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
                'tone' => 'from-emerald-500 to-teal-600',
                'badge' => 'Sales',
            ],
            [
                'name' => 'Current Stock',
                'route' => 'reports.inventory.current-stock',
                'desc' => 'Warehouse stock levels and total value',
                'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                'tone' => 'from-indigo-500 to-violet-600',
                'badge' => 'Inventory',
            ],
            [
                'name' => 'Outstanding Balance',
                'route' => 'reports.customers.outstanding',
                'desc' => 'Customer receivables with aging breakdown',
                'icon' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z',
                'tone' => 'from-sky-500 to-blue-600',
                'badge' => 'Customers',
            ],
            [
                'name' => 'Low Stock',
                'route' => 'reports.inventory.low-stock',
                'desc' => 'Items at or below minimum stock level',
                'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                'tone' => 'from-amber-500 to-orange-600',
                'badge' => 'Alerts',
            ],
        ];

        $moreGroups = [
            [
                'title' => 'More Sales Reports',
                'items' => [
                    ['name' => 'Monthly Sales', 'route' => 'reports.sales.monthly', 'desc' => 'Month-wise summary with chart'],
                    ['name' => 'Customer-wise Sales', 'route' => 'reports.sales.customer-wise', 'desc' => 'Sales grouped by customer'],
                    ['name' => 'Product-wise Sales', 'route' => 'reports.sales.product-wise', 'desc' => 'Best sellers and returns'],
                ],
            ],
            [
                'title' => 'More Inventory Reports',
                'items' => [
                    ['name' => 'Stock Movement', 'route' => 'reports.inventory.stock-movement', 'desc' => 'Full stock in/out ledger'],
                    ['name' => 'Damaged Stock', 'route' => 'reports.inventory.damaged-stock', 'desc' => 'Damage quantity and value'],
                ],
            ],
            [
                'title' => 'More Customer Reports',
                'items' => [
                    ['name' => 'Customer Ledger', 'route' => 'reports.customers.ledger', 'desc' => 'Running debit/credit balance'],
                ],
            ],
        ];

        return Inertia::render('ReportManagement/Index', [
            'featured' => $featured,
            'moreGroups' => $moreGroups,
            'historyLinks' => [
                [
                    'name' => 'Sales History',
                    'route' => 'sales-history.index',
                    'desc' => 'Full sales log with filters and export',
                    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                ],
                [
                    'name' => 'Expense History',
                    'route' => 'expense-history.index',
                    'desc' => 'Operating expenses with dashboard and export',
                    'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                ],
            ],
        ]);
    }
}
