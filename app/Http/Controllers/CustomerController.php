<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Sale;
use App\Services\CustomerLedgerService;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerLedgerService $ledgerService,
        protected CustomerService $customerService
    ) {
    }

    public function index(Request $request)
    {
        return $this->renderCustomerIndex($request, [
            'title' => 'Customers',
            'subtitle' => 'Manage customer accounts, balances, and transaction history.',
            'indexRoute' => 'customers.index',
            'lockedType' => null,
        ]);
    }

    public function wholesale(Request $request)
    {
        $request->merge(['customer_type' => 'wholesale']);

        return $this->renderCustomerIndex($request, [
            'title' => 'Wholesale Customers',
            'subtitle' => 'Wholesale account list and balances.',
            'indexRoute' => 'customers.wholesale',
            'lockedType' => 'wholesale',
        ]);
    }

    public function retail(Request $request)
    {
        $request->merge(['customer_type' => 'retail']);

        return $this->renderCustomerIndex($request, [
            'title' => 'Retail Customers',
            'subtitle' => 'Retail account list and balances.',
            'indexRoute' => 'customers.retail',
            'lockedType' => 'retail',
        ]);
    }

    protected function renderCustomerIndex(Request $request, array $meta)
    {
        $filters = $request->only('search', 'customer_type', 'status', 'date_from', 'date_to');

        $customers = Customer::query()
            ->with('user')
            ->tap(fn ($q) => $this->customerService->applyFilters($q, $filters))
            ->tap(fn ($q) => $this->customerService->defaultListOrder($q))
            ->paginate(15)
            ->withQueryString();

        $customers->getCollection()->transform(function (Customer $customer) {
            $customer->outstanding = $this->ledgerService->getOutstanding((int) $customer->id);
            $deleteCheck = $this->customerService->canDelete($customer);
            $customer->can_delete = $deleteCheck['allowed'];
            $customer->delete_blocked_reason = $deleteCheck['reason'];

            return $customer;
        });

        return Inertia::render('InventoryManagement/Customers/Index', [
            'customers' => $customers,
            'summary' => $this->customerService->dashboardSummary($filters),
            'recentTransactions' => CustomerLedger::with('customer:id,customer_name,customer_code')
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->limit(8)
                ->get(),
            'filters' => $filters,
            'customerTypes' => CustomerService::UI_CUSTOMER_TYPES,
            'pageTitle' => $meta['title'],
            'pageSubtitle' => $meta['subtitle'],
            'indexRoute' => $meta['indexRoute'],
            'lockedType' => $meta['lockedType'],
        ]);
    }

    public function create(Request $request)
    {
        $defaultType = $request->query('customer_type');
        if (! in_array($defaultType, CustomerService::UI_CUSTOMER_TYPES, true)) {
            $defaultType = 'retail';
        }

        return Inertia::render('InventoryManagement/Customers/Create', [
            'customerTypes' => CustomerService::UI_CUSTOMER_TYPES,
            'defaultType' => $defaultType,
            'generatedCode' => $this->customerService->generateCustomerCode(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_code' => 'required|string|unique:customers,customer_code',
            'customer_type' => 'required|in:' . implode(',', CustomerService::UI_CUSTOMER_TYPES),
            'company_name' => 'nullable|string|max:255',
            'customer_name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:30', Rule::unique('customers', 'phone')->whereNull('deleted_at')],
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        DB::transaction(function () use ($request) {
            $customer = Customer::create([
                'user_id' => Auth::id(),
                'customer_code' => $request->customer_code,
                'customer_type' => $request->customer_type,
                'company_name' => $request->company_name,
                'customer_name' => $request->customer_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'city' => $request->city,
                'opening_balance' => $request->input('opening_balance', 0),
                'opening_balance_type' => 'debit',
                'credit_limit' => 0,
                'remarks' => $request->remarks,
                'status' => $request->boolean('status', true),
            ]);

            $this->syncOpeningBalanceLedger($customer);
        });

        return redirect()->route('customers.index')->with('success', 'Customer created successfully');
    }

    public function show(string $id)
    {
        $customer = Customer::with([
            'user',
            'ledgers' => fn ($q) => $q->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->limit(10),
        ])->findOrFail($id);

        return Inertia::render('InventoryManagement/Customers/Show', [
            'customer' => $customer,
            'profile' => $this->customerService->profileSnapshot($customer),
        ]);
    }

    public function ledger(string $id, Request $request)
    {
        $customer = Customer::findOrFail($id);

        $ledgers = CustomerLedger::query()
            ->where('customer_id', $customer->id)
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('transaction_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('transaction_date', '<=', $request->date_to))
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('InventoryManagement/Customers/Ledger', [
            'customer' => $customer,
            'ledgers' => $ledgers,
            'profile' => $this->customerService->profileSnapshot($customer),
            'filters' => $request->only('date_from', 'date_to'),
        ]);
    }

    public function edit(string $id)
    {
        $customer = Customer::findOrFail($id);

        return Inertia::render('InventoryManagement/Customers/Edit', [
            'customer' => $customer,
            'customerTypes' => $this->customerService->allowedTypesFor($customer),
            'outstanding' => $this->ledgerService->getOutstanding((int) $customer->id),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);
        $allowedTypes = $this->customerService->allowedTypesFor($customer);

        $request->validate([
            'customer_type' => 'required|in:' . implode(',', $allowedTypes),
            'company_name' => 'nullable|string|max:255',
            'customer_name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:30', Rule::unique('customers', 'phone')->ignore($id)->whereNull('deleted_at')],
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $customer) {
            $customer->update([
                'customer_type' => $request->customer_type,
                'company_name' => $request->company_name,
                'customer_name' => $request->customer_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'city' => $request->city,
                'opening_balance' => $request->input('opening_balance', 0),
                'opening_balance_type' => $customer->opening_balance_type ?: 'debit',
                'remarks' => $request->remarks,
                'status' => $request->boolean('status'),
            ]);

            $this->syncOpeningBalanceLedger($customer->fresh());
        });

        return redirect()->route('customers.show', $customer->id)->with('success', 'Customer updated successfully');
    }

    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);
        $deleteCheck = $this->customerService->canDelete($customer);

        if (! $deleteCheck['allowed']) {
            return redirect()->route('customers.index')->with('danger', $deleteCheck['reason']);
        }

        DB::transaction(function () use ($customer) {
            $this->ledgerService->deleteAllForCustomer((int) $customer->id);
            $customer->delete();
        });

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully');
    }

    public function openingBalances(Request $request)
    {
        return redirect()->route('customers.index', $request->only('search', 'customer_type', 'status', 'date_from', 'date_to'));
    }

    public function outstanding(Request $request)
    {
        return redirect()->route('customers.index', $request->only('search', 'customer_type', 'status', 'date_from', 'date_to'));
    }

    public function salesHistory(Request $request)
    {
        $sales = Sale::query()
            ->with(['customer:id,customer_name,customer_code,customer_type', 'warehouse:id,name'])
            ->whereNotNull('customer_id')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($c) use ($search) {
                            $c->where('customer_name', 'like', "%{$search}%")
                                ->orWhere('customer_code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->filled('sale_status'), fn ($q) => $q->where('sale_status', $request->sale_status))
            ->latest('sale_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('InventoryManagement/Customers/SalesHistory', [
            'sales' => $sales,
            'customers' => Customer::select('id', 'customer_name', 'customer_code')->tap(fn ($q) => $this->customerService->defaultListOrder($q))->get(),
            'filters' => $request->only('search', 'customer_id', 'sale_status'),
            'saleStatuses' => ['draft', 'completed', 'cancelled'],
        ]);
    }

    protected function syncOpeningBalanceLedger(Customer $customer): void
    {
        $this->ledgerService->reverse('customers', (int) $customer->id);

        if ((float) $customer->opening_balance <= 0) {
            return;
        }

        $debit = $customer->opening_balance_type === 'debit' ? (float) $customer->opening_balance : 0;
        $credit = $customer->opening_balance_type === 'credit' ? (float) $customer->opening_balance : 0;

        $this->ledgerService->post(
            (int) $customer->id,
            'opening_balance',
            now()->toDateString(),
            'customers',
            (int) $customer->id,
            $customer->customer_code,
            $debit,
            $credit,
            'Opening balance'
        );
    }
}
