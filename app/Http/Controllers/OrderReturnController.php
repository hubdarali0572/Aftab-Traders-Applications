<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderReturnRequest;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Services\OrderConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;
use Throwable;

class OrderReturnController extends Controller
{
    protected array $returnStatuses = ['draft', 'approved', 'cancelled'];

    public function __construct(
        protected OrderConversionService $conversion
    ) {
    }

    public function index(Request $request)
    {
        $returns = OrderReturn::query()
            ->with(['order', 'customer', 'warehouse', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('order', fn ($o) => $o->where('order_no', 'like', "%{$search}%"))
                        ->orWhereHas('customer', fn ($c) => $c->where('customer_name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('return_status'), fn ($q) => $q->where('return_status', $request->return_status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/OrderReturns/Index', [
            'returns' => $returns,
            'filters' => $request->only('search', 'return_status'),
            'returnStatuses' => $this->returnStatuses,
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/OrderReturns/Create', [
            'orders' => $this->ordersForForms(),
            'returnStatuses' => $this->returnStatuses,
        ]);
    }

    public function store(OrderReturnRequest $request)
    {
        $order = Order::with(['customer:id,customer_name', 'warehouse:id,name'])
            ->findOrFail($request->order_id);

        if ($order->order_status === 'cancelled') {
            return redirect()->back()->withInput()->with('error', 'Cannot create a return against a cancelled order.');
        }

        OrderReturn::create([
            'user_id' => Auth::id(),
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'warehouse_id' => $order->warehouse_id,
            'reference_no' => $request->reference_no,
            'return_date' => $request->return_date,
            'total_quantity' => 0,
            'total_amount' => 0,
            'return_status' => $request->return_status,
            'remarks' => $request->remarks,
            'status' => $request->boolean('status', true),
        ]);

        return redirect()->route('order-returns.index')->with('success', 'Order return created successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/OrderReturns/Show', [
            'orderReturn' => OrderReturn::with([
                'order',
                'customer',
                'warehouse',
                'user',
                'details.product',
                'details.unit',
                'convertedSaleReturn:id,reference_no',
            ])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        $orderReturn = OrderReturn::findOrFail($id);

        if ($orderReturn->converted_sale_return_id) {
            return redirect()->route('order-returns.show', $orderReturn->id)
                ->with('error', 'Converted order returns cannot be edited.');
        }

        return Inertia::render('InventoryManagement/OrderReturns/Edit', [
            'orderReturn' => $orderReturn,
            'orders' => $this->ordersForForms(),
            'returnStatuses' => $this->returnStatuses,
        ]);
    }

    public function update(OrderReturnRequest $request, string $id)
    {
        $orderReturn = OrderReturn::withCount('details')->findOrFail($id);

        if ($orderReturn->converted_sale_return_id) {
            return redirect()->back()->with('error', 'Converted order returns cannot be updated.');
        }

        $order = Order::findOrFail($request->order_id);

        if ($orderReturn->details_count > 0 && (int) $orderReturn->order_id !== (int) $order->id) {
            return redirect()->back()->withInput()->with('error', 'You cannot change the order once return line items have been added.');
        }

        $orderReturn->update([
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'warehouse_id' => $order->warehouse_id,
            'reference_no' => $request->reference_no,
            'return_date' => $request->return_date,
            'return_status' => $request->return_status,
            'remarks' => $request->remarks,
            'status' => $request->boolean('status', true),
        ]);

        return redirect()->route('order-returns.index')->with('success', 'Order return updated successfully');
    }

    public function destroy(string $id)
    {
        $orderReturn = OrderReturn::with('details')->findOrFail($id);

        if ($orderReturn->converted_sale_return_id) {
            return redirect()->back()->with('error', 'Converted order returns cannot be deleted.');
        }

        DB::transaction(function () use ($orderReturn) {
            foreach ($orderReturn->details as $detail) {
                $detail->delete();
            }
            $orderReturn->delete();
        });

        return redirect()->back()->with('success', 'Order return deleted successfully');
    }

    public function convertToSaleReturn(string $id)
    {
        $orderReturn = OrderReturn::with(['details', 'order'])->findOrFail($id);

        try {
            $saleReturn = DB::transaction(
                fn () => $this->conversion->convertOrderReturnToSaleReturn($orderReturn)
            );
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Could not convert order return: ' . $e->getMessage());
        }

        return redirect()->route('sale-returns.show', $saleReturn->id)
            ->with('success', 'Order return converted to sales return ' . $saleReturn->reference_no . ' successfully');
    }

    protected function ordersForForms()
    {
        return Order::with(['customer:id,customer_name', 'warehouse:id,name'])
            ->select('id', 'order_no', 'customer_id', 'warehouse_id', 'order_status', 'converted_sale_id')
            ->where('order_status', '!=', 'cancelled')
            ->latest()
            ->get();
    }
}
