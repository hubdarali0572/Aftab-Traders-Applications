<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Services\CustomerLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerLedgerController extends Controller
{
    protected array $transactionTypes = [
        'opening_balance', 'sale', 'sale_return', 'payment_received',
        'debit_note', 'credit_note', 'adjustment',
    ];

    public function __construct(
        protected CustomerLedgerService $ledgerService
    ) {
    }

    public function index(Request $request)
    {
        $ledgers = CustomerLedger::query()
            ->with(['customer', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('customer_name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('transaction_type'), fn ($q) => $q->where('transaction_type', $request->transaction_type))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('InventoryManagement/CustomerLedgers/Index', [
            'ledgers' => $ledgers,
            'customers' => Customer::select('id', 'customer_name', 'customer_code')->get(),
            'filters' => $request->only('search', 'customer_id', 'transaction_type'),
            'transactionTypes' => $this->transactionTypes,
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('InventoryManagement/CustomerLedgers/Create', [
            'customers' => Customer::select('id', 'customer_name', 'customer_code')->orderBy('customer_name')->get(),
            'transactionTypes' => $this->transactionTypes,
            'defaultCustomerId' => $request->query('customer_id'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:' . implode(',', $this->transactionTypes),
            'reference_no' => 'nullable|string|max:255',
            'debit' => 'required|numeric|min:0',
            'credit' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        if ((float) $request->debit <= 0 && (float) $request->credit <= 0) {
            return redirect()->back()->withInput()->with('error', 'Either debit or credit amount must be greater than zero.');
        }

        DB::transaction(function () use ($request) {
            $entry = CustomerLedger::create([
                'user_id' => Auth::id(),
                'customer_id' => $request->customer_id,
                'transaction_date' => $request->transaction_date,
                'transaction_type' => $request->transaction_type,
                'reference_type' => 'Manual',
                'reference_id' => 0,
                'reference_no' => $request->reference_no,
                'debit' => $request->debit,
                'credit' => $request->credit,
                'balance' => 0,
                'remarks' => $request->remarks,
                'status' => $request->boolean('status', true),
            ]);

            $entry->update(['reference_id' => $entry->id]);
            $this->ledgerService->recalculateBalances((int) $request->customer_id);
        });

        return redirect()->route('customer-ledgers.index')->with('success', 'Ledger entry recorded successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/CustomerLedgers/Show', [
            'ledger' => CustomerLedger::with(['customer', 'user'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        $ledger = CustomerLedger::findOrFail($id);

        if ($ledger->reference_type !== 'Manual') {
            return redirect()->route('customer-ledgers.show', $id)
                ->with('error', 'System-generated ledger entries cannot be edited.');
        }

        return Inertia::render('InventoryManagement/CustomerLedgers/Edit', [
            'ledger' => $ledger,
            'customers' => Customer::select('id', 'customer_name', 'customer_code')->get(),
            'transactionTypes' => $this->transactionTypes,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $ledger = CustomerLedger::findOrFail($id);

        if ($ledger->reference_type !== 'Manual') {
            return redirect()->back()->with('error', 'System-generated ledger entries cannot be edited.');
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:' . implode(',', $this->transactionTypes),
            'reference_no' => 'nullable|string|max:255',
            'debit' => 'required|numeric|min:0',
            'credit' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        if ((float) $request->debit <= 0 && (float) $request->credit <= 0) {
            return redirect()->back()->withInput()->with('error', 'Either debit or credit amount must be greater than zero.');
        }

        $oldCustomerId = (int) $ledger->customer_id;

        DB::transaction(function () use ($request, $ledger, $oldCustomerId) {
            $ledger->update([
                'customer_id' => $request->customer_id,
                'transaction_date' => $request->transaction_date,
                'transaction_type' => $request->transaction_type,
                'reference_no' => $request->reference_no,
                'debit' => $request->debit,
                'credit' => $request->credit,
                'remarks' => $request->remarks,
                'status' => $request->boolean('status', true),
            ]);

            $this->ledgerService->recalculateBalances($oldCustomerId);
            if ((int) $request->customer_id !== $oldCustomerId) {
                $this->ledgerService->recalculateBalances((int) $request->customer_id);
            }
        });

        return redirect()->route('customer-ledgers.index')->with('success', 'Ledger entry updated successfully');
    }

    public function destroy(string $id)
    {
        $ledger = CustomerLedger::findOrFail($id);

        if ($ledger->reference_type !== 'Manual') {
            return redirect()->back()->with('error', 'System-generated ledger entries cannot be deleted.');
        }

        $customerId = (int) $ledger->customer_id;

        DB::transaction(function () use ($ledger, $customerId) {
            $ledger->delete();
            $this->ledgerService->recalculateBalances($customerId);
        });

        return redirect()->back()->with('success', 'Ledger entry deleted successfully');
    }
}
