<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockHistoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockHistoryController extends Controller
{
    public function __construct(
        protected StockHistoryService $history
    ) {
    }

    public function index(Request $request)
    {
        return Inertia::render('InventoryManagement/StockHistory/Index', [
            'transferHistory' => $this->history->transferHistory($request),
            'damagedHistory' => $this->history->damagedHistory($request),
            'summary' => $this->history->summary($request),
            'warehouses' => Warehouse::select('id', 'name')->orderBy('name')->get(),
            'products' => Product::select('id', 'name', 'sku')->orderBy('name')->get(),
            'filters' => $request->only($this->history->filterKeys()),
        ]);
    }

    public function showTransfer(string $transfer)
    {
        return Inertia::render('InventoryManagement/StockHistory/TransferShow', [
            'transfer' => $this->history->transferDetail((int) $transfer),
        ]);
    }

    public function showDamaged(string $item)
    {
        return Inertia::render('InventoryManagement/StockHistory/DamagedShow', [
            'record' => $this->history->damagedItemDetail((int) $item),
        ]);
    }
}
