<?php

namespace App\Http\Controllers;

use App\Models\Customer;
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
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/Customers/Create', [
            'customerTypes' => $this->customerTypes,
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

            if ((float) $request->opening_balance > 0) {
                $debit = $request->opening_balance_type === 'debit' ? (float) $request->opening_balance : 0;
                $credit = $request->opening_balance_type === 'credit' ? (float) $request->opening_balance : 0;

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
        });

        return redirect()->route('customers.index')->with('success', 'Customer created successfully');
    }

    public function show(string $id)
    {
        $customer = Customer::with(['user', 'ledgers' => fn ($q) => $q->latest()->limit(10)])
            ->findOrFail($id);

        $outstanding = $this->ledgerService->getOutstanding((int) $customer->id);

        return Inertia::render('InventoryManagement/Customers/Show', [
            'customer' => $customer,
            'outstanding' => $outstanding,
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

        $customer->update($request->all());

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully');
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
}
