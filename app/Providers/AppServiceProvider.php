<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        if ($this->shouldForceHttps()) {
            URL::forceScheme('https');
        }

        Relation::enforceMorphMap([
            'user' => 'App\Models\User',
            'brand' => 'App\Models\Brand',
            'product-category' => 'App\Models\ProductCategory',
            'Unit' => 'App\Models\Unit',
            'product' => 'App\Models\Product',
            'product-selling-price' => 'App\Models\ProductSellingPrice',
            'warehouse' => 'App\Models\Warehouse',
            'warehouse-stock' => 'App\Models\Stock',
            'stock' => 'App\Models\Stock',
            'stock-movement' => 'App\Models\StockMovement',
            'opening-stock' => 'App\Models\OpeningStock',
            'opening-stock-item' => 'App\Models\OpeningStockItem',
            'stock-adjustment' => 'App\Models\StockAdjustment',
            'stock-adjustment-item' => 'App\Models\StockAdjustmentItem',
            'stock-transfer' => 'App\Models\StockTransfer',
            'damaged-stock' => 'App\Models\DamagedStock',
            'damaged-stock-item' => 'App\Models\DamagedStockItem',
            'purchases' => 'App\Models\Purchase',
            'purchase-details' => 'App\Models\PurchaseDetail',
            'purchase-return' => 'App\Models\PurchaseReturn',
            'purchase-return-detail' => 'App\Models\PurchaseReturnDetail',
            'purchase-expense' => 'App\Models\PurchaseExpense',
            'purchase-transaction' => 'App\Models\PurchaseTransaction',
            'customers' => 'App\Models\Customer',
            'customer-ledgers' => 'App\Models\CustomerLedger',
            'sales' => 'App\Models\Sale',
            'sale-details' => 'App\Models\SaleDetail',
            'sale-return' => 'App\Models\SaleReturn',
            'sale-return-detail' => 'App\Models\SaleReturnDetail',
            'orders' => 'App\Models\Order',
            'order-details' => 'App\Models\OrderDetail',
            'order-return' => 'App\Models\OrderReturn',
            'order-return-detail' => 'App\Models\OrderReturnDetail',
            'expense-head' => 'App\Models\ExpenseHead',
            'expense' => 'App\Models\Expense',
            // Add other models here as needed
        ]);
    }

    private function shouldForceHttps(): bool
    {
        if (filter_var(config('app.force_https'), FILTER_VALIDATE_BOOL)) {
            return true;
        }

        return str_starts_with((string) config('app.url'), 'https://');
    }
}
