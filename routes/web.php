<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerLedgerController;
use App\Http\Controllers\DamagedStockController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseHeadController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\OpeningStockController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\OrderReturnController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSellingPriceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseDetailController;
use App\Http\Controllers\PurchaseExpenseController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\SaleHistoryController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\PurchaseReturnDetailController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockHistoryController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\Reports\CustomerReportController;
use App\Http\Controllers\Reports\FinancialReportController;
use App\Http\Controllers\Reports\InventoryReportController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Reports\SalesReportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

require __DIR__ . '/public-site.php';

// activitylogs Route

// Main page to see all logs
Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity.index');
Route::delete('/activity-logs/{id}', [ActivityLogController::class, 'destroy'])->name('activity.destroy');
Route::delete('/activity-logs-clear', [ActivityLogController::class, 'clearAll'])->name('activity.clear');

// Your existing API route for modals
Route::get('/api/activities/{module}/{id}', [ActivityLogController::class, 'getLogs']);

Route::get('/media/{media}/{conversion?}', [MediaController::class, 'show'])
    ->name('media.show');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    // User Routes
    Route::resource('users', UserController::class);
    // Role Routes
    Route::resource('roles', RoleController::class);
    // Brand Routes
    Route::resource('brands', BrandController::class);
    // ProductCategory Routes
    Route::resource('product-categories', ProductCategoryController::class);
    // Unit Routes
    Route::resource('units', UnitController::class);
    // products Routes
    Route::resource('products', ProductController::class);
    // product-selling-prices Routes
    Route::resource('product-selling-prices', ProductSellingPriceController::class);
    // warehouse Routes
    Route::resource('warehouses', WarehouseController::class);
    // stocks Routes (quantities auto-updated; create registers warehouse/product thresholds)
    Route::resource('stocks', StockController::class)->except(['destroy']);
    Route::get('stock-history', [StockHistoryController::class, 'index'])->name('stock-history.index');
    Route::get('stock-history/transfers/{transfer}', [StockHistoryController::class, 'showTransfer'])->name('stock-history.transfers.show');
    Route::get('stock-history/damaged/{item}', [StockHistoryController::class, 'showDamaged'])->name('stock-history.damaged.show');
    // opening_stocks Routes
    Route::resource('opening-stocks', OpeningStockController::class);
    // stock_adjustments Routes
    Route::resource('stock-adjustments', StockAdjustmentController::class);
    // stock_transfers Routes
    Route::resource('stock-transfers', StockTransferController::class);
    // damaged_stocks Routes
    Route::resource('damaged-stocks', DamagedStockController::class);
    // purchases Routes
    Route::resource('purchases', PurchaseController::class);
    // purchase line items (legacy routes redirect to purchases module)
    Route::resource('purchase-details', PurchaseDetailController::class);
    // purchase Return Routes
    Route::resource('purchase-returns', PurchaseReturnController::class);
    // purchase history (read-only financial timeline)
    Route::get('purchase-history', [PurchaseHistoryController::class, 'index'])->name('purchase-history.index');
    Route::get('purchase-history/{purchase}', [PurchaseHistoryController::class, 'show'])->name('purchase-history.show');
    // purchase return line items (legacy routes redirect to purchase-returns module)
    Route::resource('purchase-return-details', PurchaseReturnDetailController::class);
    // purchase Expenses (legacy routes redirect to purchases module)
    Route::resource('purchase-expenses', PurchaseExpenseController::class);
    // customers Routes
    Route::get('customers/wholesale', [CustomerController::class, 'wholesale'])->name('customers.wholesale');
    Route::get('customers/retail', [CustomerController::class, 'retail'])->name('customers.retail');
    Route::get('customers/opening-balances', [CustomerController::class, 'openingBalances'])->name('customers.opening-balances');
    Route::get('customers/outstanding', [CustomerController::class, 'outstanding'])->name('customers.outstanding');
    Route::get('customers/sales-history', [CustomerController::class, 'salesHistory'])->name('customers.sales-history');
    Route::get('customers/{customer}/ledger', [CustomerController::class, 'ledger'])->name('customers.customer-ledger');
    Route::resource('customers', CustomerController::class);
    // customer Ledger Routes
    Route::resource('customer-ledgers', CustomerLedgerController::class);
    // sales Routes
    Route::resource('sales', SaleController::class);
    // sale Return Routes
    Route::resource('sale-returns', SaleReturnController::class);
    Route::get('sales-history', [SaleHistoryController::class, 'index'])->name('sales-history.index');
    Route::redirect('sale-details', '/sales')->name('sale-details.index');
    Route::redirect('sale-details/create', '/sales/create')->name('sale-details.create');
    Route::redirect('sale-return-details', '/sale-returns')->name('sale-return-details.index');
    Route::redirect('sale-return-details/create', '/sale-returns/create')->name('sale-return-details.create');

    // Order Management
    Route::resource('orders', OrderController::class);
    Route::resource('order-returns', OrderReturnController::class);
    Route::get('orders-history', [OrderHistoryController::class, 'index'])->name('orders-history.index');
    Route::redirect('order-details', '/orders')->name('order-details.index');
    Route::redirect('order-details/create', '/orders/create')->name('order-details.create');
    Route::redirect('order-return-details', '/order-returns')->name('order-return-details.index');
    Route::redirect('order-return-details/create', '/order-returns/create')->name('order-return-details.create');

    Route::resource('expense-heads', ExpenseHeadController::class);
    Route::resource('expenses', ExpenseController::class);

    // Report Management
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');

        Route::get('/sales/daily', [SalesReportController::class, 'daily'])->name('sales.daily');
        Route::get('/sales/monthly', [SalesReportController::class, 'monthly'])->name('sales.monthly');
        Route::get('/sales/customer-wise', [SalesReportController::class, 'customerWise'])->name('sales.customer-wise');
        Route::get('/sales/product-wise', [SalesReportController::class, 'productWise'])->name('sales.product-wise');

        Route::get('/inventory/current-stock', [InventoryReportController::class, 'currentStock'])->name('inventory.current-stock');
        Route::get('/inventory/low-stock', [InventoryReportController::class, 'lowStock'])->name('inventory.low-stock');
        Route::get('/inventory/stock-movement', [InventoryReportController::class, 'stockMovement'])->name('inventory.stock-movement');
        Route::get('/inventory/damaged-stock', [InventoryReportController::class, 'damagedStock'])->name('inventory.damaged-stock');

        Route::get('/customers/ledger', [CustomerReportController::class, 'ledger'])->name('customers.ledger');
        Route::get('/customers/outstanding', [CustomerReportController::class, 'outstanding'])->name('customers.outstanding');
        Route::get('/customers/payment-history', [CustomerReportController::class, 'paymentHistory'])->name('customers.payment-history');
        Route::get('/customers/sales-history', [CustomerReportController::class, 'salesHistory'])->name('customers.sales-history');

        Route::get('/financial/expenses', [FinancialReportController::class, 'expenses'])->name('financial.expenses');
        Route::get('/financial/profit-loss', [FinancialReportController::class, 'profitLoss'])->name('financial.profit-loss');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
