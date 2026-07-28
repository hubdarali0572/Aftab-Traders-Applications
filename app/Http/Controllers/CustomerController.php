<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\Services\CustomerLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerController extends Controller
{
    protected array $customerTypes = [
        'retail', 'wholesale', 'dealer', 'distributor',
        'corporate', 'contractor', 'reseller', 'walk_in',
    ];

    public function __construct(
        protected CustomerLedgerService $ledgerService
    ) {
    }

    public function index(Request $request)
    {
        return $this->renderCustomerIndex($request, [
            'title' => 'Customers',
            'subtitle' => 'Manage customer accounts and contact information.',
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
        $customers = Customer::query()
            ->with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('customer_code', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('customer_type'), fn ($q) => $q->where('customer_type', $request->customer_type))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('InventoryManagement/Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only('search', 'customer_type'),
            'customerTypes' => $this->customerTypes,
            'pageTitle' => $meta['title'],
            'pageSubtitle' => $meta['subtitle'],
            'indexRoute' => $meta['indexRoute'],
            'lockedType' => $meta['lockedType'],
        ]);
    }

    public function create(Request $request)
    {
        $defaultType = $request->query('customer_type');
        if (! in_array($defaultType, $this->customerTypes, true)) {
            $defaultType = 'retail';
        }

        return Inertia::render('InventoryManagement/Customers/Create', [
            'customerTypes' => $this->customerTypes,
            'defaultType' => $defaultType,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_code' => 'required|string|unique:customers,customer_code',
            'customer_type' => 'required|in:' . implode(',', $this->customerTypes),
            'company_name' => 'nullable|string|max:255',
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'alternate_phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'opening_balance' => 'required|numeric|min:0',
            'opening_balance_type' => 'required|in:debit,credit',
            'credit_limit' => 'required|numeric|min:0',
            'tax_number' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        DB::transaction(function () use ($request) {
            $customer = Customer::create(array_merge($request->all(), ['user_id' => Auth::id()]));

            $this->syncOpeningBalanceLedger($customer);
        });

        return redirect()->route('customers.index')->with('success', 'Customer created successfully');
    }

    public function show(string $id)
    {
        $customer = Customer::with([
            'user',
            'ledgers' => fn ($q) => $q->latest('transaction_date')->latest('id')->limit(10),
            'sales' => fn ($q) => $q->with('warehouse:id,name')->latest('sale_date')->latest('id')->limit(20),
        ])->findOrFail($id);

        $outstanding = $this->ledgerService->getOutstanding((int) $customer->id);

        $salesSummary = [
            'total_invoices' => $customer->sales()->count(),
            'completed_amount' => (float) $customer->sales()->where('sale_status', 'completed')->sum('grand_total'),
            'due_amount' => (float) $customer->sales()->where('sale_status', 'completed')->sum('due_amount'),
        ];

        return Inertia::render('InventoryManagement/Customers/Show', [
            'customer' => $customer,
            'outstanding' => $outstanding,
            'salesSummary' => $salesSummary,
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/Customers/Edit', [
            'customer' => Customer::findOrFail($id),
            'customerTypes' => $this->customerTypes,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'customer_code' => 'required|string|unique:customers,customer_code,' . $id,
            'customer_type' => 'required|in:' . implode(',', $this->customerTypes),
            'company_name' => 'nullable|string|max:255',
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'alternate_phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'opening_balance' => 'required|numeric|min:0',
            'opening_balance_type' => 'required|in:debit,credit',
            'credit_limit' => 'required|numeric|min:0',
            'tax_number' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $customer) {
            $customer->update($request->all());
            $this->syncOpeningBalanceLedger($customer->fresh());
        });

        return redirect()->route('customers.show', $customer->id)->with('success', 'Customer updated successfully');
    }

    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);

        if ($customer->sales()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete a customer that has sales records.');
        }

        DB::transaction(function () use ($customer) {
            $this->ledgerService->reverse('customers', (int) $customer->id);
            $customer->delete();
        });

        return redirect()->back()->with('success', 'Customer deleted successfully');
    }

    public function openingBalances(Request $request)
    {
        $customers = Customer::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('customer_code', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('customer_type'), fn ($q) => $q->where('customer_type', $request->customer_type))
            ->orderByDesc('opening_balance')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('InventoryManagement/Customers/OpeningBalances', [
            'customers' => $customers,
            'filters' => $request->only('search', 'customer_type'),
            'customerTypes' => $this->customerTypes,
        ]);
    }

    public function outstanding(Request $request)
    {
        $customers = Customer::query()
            ->with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('customer_code', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('customer_type'), fn ($q) => $q->where('customer_type', $request->customer_type))
            ->get()
            ->map(function (Customer $customer) {
                $customer->outstanding = $this->ledgerService->getOutstanding((int) $customer->id);

                return $customer;
            })
            ->filter(fn (Customer $customer) => abs((float) $customer->outstanding) > 0.0001)
            ->sortByDesc(fn (Customer $customer) => abs((float) $customer->outstanding))
            ->values();

        if ($request->filled('only_due') && $request->boolean('only_due')) {
            $customers = $customers->filter(fn (Customer $customer) => (float) $customer->outstanding > 0)->values();
        }

        $totalOutstanding = (float) $customers->sum(fn (Customer $c) => max(0, (float) $c->outstanding));

        return Inertia::render('InventoryManagement/Customers/Outstanding', [
            'customers' => $customers,
            'totalOutstanding' => $totalOutstanding,
            'filters' => $request->only('search', 'customer_type', 'only_due'),
            'customerTypes' => $this->customerTypes,
        ]);
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
            'customers' => Customer::select('id', 'customer_name', 'customer_code')->orderBy('customer_name')->get(),
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
