<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerLedgerController;
use App\Http\Controllers\DamagedStockController;
use App\Http\Controllers\DamagedStockDetailController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\OpeningStockController;
use App\Http\Controllers\OpeningStockDetailController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderDetailController;
use App\Http\Controllers\OrderReturnController;
use App\Http\Controllers\OrderReturnDetailController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSellingPriceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseDetailController;
use App\Http\Controllers\PurchaseExpenseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\PurchaseReturnDetailController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleDetailController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\SaleReturnDetailController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockAdjustmentDetailController;
use App\Http\Controllers\StockLedgerController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\StockTransferDetailController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseStockController;
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
    // warehouse-stocks Routes
    Route::resource('warehouse-stocks', WarehouseStockController::class);
    // stock-ledgers Routes
    Route::resource('stock-ledgers', StockLedgerController::class);
    // opening_stocks Routes
    Route::resource('opening-stocks', OpeningStockController::class);
    // opening_stocks Routes
    Route::resource('opening-stock-details', OpeningStockDetailController::class);
    // stock_adjustments Routes
    Route::resource('stock-adjustments', StockAdjustmentController::class);
    // stock_adjustment_details Routes
    Route::resource('stock-adjustment-details', StockAdjustmentDetailController::class);
    // stock_transfers Routes
    Route::resource('stock-transfers', StockTransferController::class);
    // stock_transfer_details Routes
    Route::resource('stock-transfer-details', StockTransferDetailController::class);
    // damaged_stocks Routes
    Route::resource('damaged-stocks', DamagedStockController::class);
    // damaged_stock_details Routes
    Route::resource('damaged-stock-details', DamagedStockDetailController::class);
    // purchases Routes
    Route::resource('purchases', PurchaseController::class);
    // purchase Detail Routes
    Route::resource('purchase-details', PurchaseDetailController::class);
    // purchase Return Routes
    Route::resource('purchase-returns', PurchaseReturnController::class);
    // purchase Return Detail Routes
    Route::resource('purchase-return-details', PurchaseReturnDetailController::class);
    // purchase Expenses Routes
    Route::resource('purchase-expenses', PurchaseExpenseController::class);
    // customers Routes
    Route::get('customers/wholesale', [CustomerController::class, 'wholesale'])->name('customers.wholesale');
    Route::get('customers/retail', [CustomerController::class, 'retail'])->name('customers.retail');
    Route::get('customers/opening-balances', [CustomerController::class, 'openingBalances'])->name('customers.opening-balances');
    Route::get('customers/outstanding', [CustomerController::class, 'outstanding'])->name('customers.outstanding');
    Route::get('customers/sales-history', [CustomerController::class, 'salesHistory'])->name('customers.sales-history');
    Route::resource('customers', CustomerController::class);
    // customer Ledger Routes
    Route::resource('customer-ledgers', CustomerLedgerController::class);
    // sales Routes
    Route::resource('sales', SaleController::class);
    // sale Detail Routes
    Route::resource('sale-details', SaleDetailController::class);
    // sale Return Routes
    Route::resource('sale-returns', SaleReturnController::class);
    // sale Return Detail Routes
    Route::resource('sale-return-details', SaleReturnDetailController::class);

    // Order Management
    Route::post('orders/{order}/convert-to-sale', [OrderController::class, 'convertToSale'])
        ->name('orders.convert-to-sale');
    Route::resource('orders', OrderController::class);
    Route::resource('order-details', OrderDetailController::class);
    Route::post('order-returns/{order_return}/convert-to-sale-return', [OrderReturnController::class, 'convertToSaleReturn'])
        ->name('order-returns.convert-to-sale-return');
    Route::resource('order-returns', OrderReturnController::class);
    Route::resource('order-return-details', OrderReturnDetailController::class);


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
