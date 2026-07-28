<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderReturnDetailRequest;
use App\Models\OrderDetail;
use App\Models\OrderReturn;
use App\Models\OrderReturnDetail;
use App\Models\Product;
use App\Models\Unit;
use App\Services\OrderConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;
use Throwable;

class OrderReturnDetailController extends Controller
{
    public function __construct(
        protected OrderConversionService $conversion
    ) {
    }

    public function index(Request $request)
    {
        $details = OrderReturnDetail::query()
            ->with(['orderReturn.order', 'product', 'unit'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('orderReturn', fn ($r) => $r->where('reference_no', 'like', "%{$search}%"))
                        ->orWhereHas('orderReturn.order', fn ($o) => $o->where('order_no', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('order_return_id'), fn ($q) => $q->where('order_return_id', $request->order_return_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('InventoryManagement/OrderReturnDetails/Index', [
            'details' => $details,
            'orderReturns' => OrderReturn::with('order:id,order_no')
                ->select('id', 'order_id', 'reference_no')
                ->latest()
                ->get(),
            'filters' => $request->only('search', 'order_return_id'),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('InventoryManagement/OrderReturnDetails/Create', [
            'orderReturns' => $this->orderReturnsForForms(),
            'products' => Product::with('unit:id,name')->select('id', 'name', 'unit_id')->orderBy('name')->get(),
            'units' => Unit::select('id', 'name')->orderBy('name')->get(),
            'defaultOrderReturnId' => $request->query('order_return_id'),
        ]);
    }

    public function store(OrderReturnDetailRequest $request)
    {
        $orderReturn = OrderReturn::with('order.details')->findOrFail($request->order_return_id);

        if ($orderReturn->converted_sale_return_id || $orderReturn->return_status === 'cancelled') {
            return redirect()->back()->withInput()->with('error', 'Cannot add items to a converted or cancelled order return.');
        }

        $orderDetail = $this->getOriginalOrderDetail($orderReturn, (int) $request->product_id);
        if (! $orderDetail) {
            return redirect()->back()->withInput()->with('error', 'Selected product does not exist on the referenced order.');
        }

        try {
            $this->ensureReturnQuantityAllowed($orderReturn, (int) $request->product_id, (float) $request->quantity);
            $pricing = $this->deriveReturnAmounts($orderDetail, (float) $request->quantity);

            DB::transaction(function () use ($request, $orderReturn, $pricing) {
                $trashed = OrderReturnDetail::onlyTrashed()
                    ->where('order_return_id', $request->order_return_id)
                    ->where('product_id', $request->product_id)
                    ->first();

                if ($trashed) {
                    $trashed->restore();
                    $trashed->update([
                        'user_id' => Auth::id(),
                        'unit_id' => $request->unit_id,
                        'quantity' => $request->quantity,
                        'unit_price' => $pricing['unit_price'],
                        'line_total' => $pricing['line_total'],
                        'reason' => $request->reason,
                        'remarks' => $request->remarks,
                        'status' => $request->boolean('status', true),
                    ]);
                } else {
                    OrderReturnDetail::create([
                        'user_id' => Auth::id(),
                        'order_return_id' => $orderReturn->id,
                        'product_id' => $request->product_id,
                        'unit_id' => $request->unit_id,
                        'quantity' => $request->quantity,
                        'unit_price' => $pricing['unit_price'],
                        'line_total' => $pricing['line_total'],
                        'reason' => $request->reason,
                        'remarks' => $request->remarks,
                        'status' => $request->boolean('status', true),
                    ]);
                }

                $this->conversion->recalcOrderReturnTotals($orderReturn->fresh());
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Could not save return item: ' . $e->getMessage());
        }

        return redirect()->route('order-return-details.index')->with('success', 'Order return line added successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/OrderReturnDetails/Show', [
            'detail' => OrderReturnDetail::with(['orderReturn.order', 'product', 'unit', 'user'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        $detail = OrderReturnDetail::with('orderReturn')->findOrFail($id);

        if ($detail->orderReturn?->converted_sale_return_id) {
            return redirect()->route('order-return-details.show', $detail->id)
                ->with('error', 'Converted return line items cannot be edited.');
        }

        return Inertia::render('InventoryManagement/OrderReturnDetails/Edit', [
            'detail' => $detail,
            'orderReturns' => $this->orderReturnsForForms((int) $detail->order_return_id),
            'products' => Product::with('unit:id,name')->select('id', 'name', 'unit_id')->orderBy('name')->get(),
            'units' => Unit::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(OrderReturnDetailRequest $request, string $id)
    {
        $detail = OrderReturnDetail::findOrFail($id);
        $orderReturn = OrderReturn::with('order.details')->findOrFail($request->order_return_id);

        if ($orderReturn->converted_sale_return_id || $orderReturn->return_status === 'cancelled') {
            return redirect()->back()->withInput()->with('error', 'Cannot update items on a converted or cancelled order return.');
        }

        $orderDetail = $this->getOriginalOrderDetail($orderReturn, (int) $request->product_id);
        if (! $orderDetail) {
            return redirect()->back()->withInput()->with('error', 'Selected product does not exist on the referenced order.');
        }

        try {
            $this->ensureReturnQuantityAllowed($orderReturn, (int) $request->product_id, (float) $request->quantity, $detail->id);
            $pricing = $this->deriveReturnAmounts($orderDetail, (float) $request->quantity);

            DB::transaction(function () use ($request, $detail, $orderReturn, $pricing) {
                $detail->update([
                    'order_return_id' => $orderReturn->id,
                    'product_id' => $request->product_id,
                    'unit_id' => $request->unit_id,
                    'quantity' => $request->quantity,
                    'unit_price' => $pricing['unit_price'],
                    'line_total' => $pricing['line_total'],
                    'reason' => $request->reason,
                    'remarks' => $request->remarks,
                    'status' => $request->boolean('status', true),
                ]);

                $this->conversion->recalcOrderReturnTotals($orderReturn->fresh());
            });
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Could not update return item: ' . $e->getMessage());
        }

        return redirect()->route('order-return-details.index')->with('success', 'Order return line updated successfully');
    }

    public function destroy(string $id)
    {
        $detail = OrderReturnDetail::with('orderReturn')->findOrFail($id);

        if ($detail->orderReturn?->converted_sale_return_id) {
            return redirect()->back()->with('error', 'Converted return line items cannot be deleted.');
        }

        DB::transaction(function () use ($detail) {
            $orderReturn = $detail->orderReturn;
            $detail->delete();
            if ($orderReturn) {
                $this->conversion->recalcOrderReturnTotals($orderReturn->fresh());
            }
        });

        return redirect()->back()->with('success', 'Order return line removed successfully');
    }

    protected function orderReturnsForForms(?int $includeId = null)
    {
        return OrderReturn::with([
            'order:id,order_no',
            'order.details:id,order_id,product_id,quantity,unit_price,discount,tax,unit_id',
        ])
            ->select('id', 'order_id', 'reference_no', 'return_status', 'converted_sale_return_id')
            ->where(function ($q) use ($includeId) {
                $q->whereNull('converted_sale_return_id')
                    ->where('return_status', '!=', 'cancelled');
                if ($includeId) {
                    $q->orWhere('id', $includeId);
                }
            })
            ->latest()
            ->get();
    }

    protected function getOriginalOrderDetail(OrderReturn $orderReturn, int $productId): ?OrderDetail
    {
        return $orderReturn->order?->details?->firstWhere('product_id', $productId)
            ?? OrderDetail::where('order_id', $orderReturn->order_id)->where('product_id', $productId)->first();
    }

    protected function ensureReturnQuantityAllowed(OrderReturn $orderReturn, int $productId, float $qty, ?int $ignoreDetailId = null): void
    {
        $orderedQty = (float) OrderDetail::where('order_id', $orderReturn->order_id)
            ->where('product_id', $productId)
            ->value('quantity');

        $returnedQty = (float) OrderReturnDetail::query()
            ->whereHas('orderReturn', fn ($q) => $q->where('order_id', $orderReturn->order_id)->where('return_status', '!=', 'cancelled'))
            ->where('product_id', $productId)
            ->when($ignoreDetailId, fn ($q) => $q->where('id', '!=', $ignoreDetailId))
            ->sum('quantity');

        if ($qty + $returnedQty > $orderedQty + 0.0001) {
            throw new InvalidArgumentException(
                'Return quantity exceeds the remaining ordered quantity. Ordered: ' . number_format($orderedQty, 2) .
                ', already returned: ' . number_format($returnedQty, 2) . '.'
            );
        }
    }

    protected function deriveReturnAmounts(OrderDetail $orderDetail, float $qty): array
    {
        $unitPrice = (float) $orderDetail->unit_price;
        $baseQty = max((float) $orderDetail->quantity, 0.01);
        $discountShare = round(((float) $orderDetail->discount / $baseQty) * $qty, 2);
        $taxShare = round(((float) $orderDetail->tax / $baseQty) * $qty, 2);
        $lineTotal = round(($qty * $unitPrice) - $discountShare + $taxShare, 2);

        return [
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
        ];
    }
}
