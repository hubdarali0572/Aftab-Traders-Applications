<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('ReportManagement/Index', [
            'groups' => [
                [
                    'title' => 'Sales Reports',
                    'description' => 'Daily, monthly, customer-wise and product-wise sales performance.',
                    'items' => [
                        ['name' => 'Daily Sales Report', 'route' => 'reports.sales.daily', 'desc' => 'Invoice-level sales for a date range'],
                        ['name' => 'Monthly Sales Report', 'route' => 'reports.sales.monthly', 'desc' => 'Month-wise sales summary with chart'],
                        ['name' => 'Customer-wise Sales', 'route' => 'reports.sales.customer-wise', 'desc' => 'Sales grouped by customer'],
                        ['name' => 'Product-wise Sales', 'route' => 'reports.sales.product-wise', 'desc' => 'Best-selling products and returns'],
                    ],
                ],
                [
                    'title' => 'Inventory Reports',
                    'description' => 'Stock position, movements, low stock and damage analysis.',
                    'items' => [
                        ['name' => 'Current Stock Report', 'route' => 'reports.inventory.current-stock', 'desc' => 'Warehouse-wise available stock & value'],
                        ['name' => 'Low Stock Report', 'route' => 'reports.inventory.low-stock', 'desc' => 'Items at or below minimum level'],
                        ['name' => 'Stock Movement Report', 'route' => 'reports.inventory.stock-movement', 'desc' => 'Full ledger of stock in/out'],
                        ['name' => 'Damaged Stock Report', 'route' => 'reports.inventory.damaged-stock', 'desc' => 'Damage quantity and value'],
                    ],
                ],
                [
                    'title' => 'Customer Reports',
                    'description' => 'Ledgers, outstanding balances, payments and sales history.',
                    'items' => [
                        ['name' => 'Customer Ledger', 'route' => 'reports.customers.ledger', 'desc' => 'Running debit/credit balance'],
                        ['name' => 'Outstanding Balance', 'route' => 'reports.customers.outstanding', 'desc' => 'Unpaid customer balances with aging'],
                        ['name' => 'Payment History', 'route' => 'reports.customers.payment-history', 'desc' => 'Payments received from customers'],
                        ['name' => 'Sales History', 'route' => 'reports.customers.sales-history', 'desc' => 'Complete purchase history by customer'],
                    ],
                ],
                [
                    'title' => 'Financial Reports',
                    'description' => 'Expenses and profit & loss from live transactions.',
                    'items' => [
                        ['name' => 'Expense Report', 'route' => 'reports.financial.expenses', 'desc' => 'Expenses by head, method and trend'],
                        ['name' => 'Profit & Loss Summary', 'route' => 'reports.financial.profit-loss', 'desc' => 'Revenue, COGS, expenses and net profit'],
                    ],
                ],
            ],
        ]);
    }
}
