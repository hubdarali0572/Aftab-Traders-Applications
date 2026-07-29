<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Warehouse;
use App\Services\ExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseService $expenses
    ) {
    }

    public function index(Request $request)
    {
        return Inertia::render('ExpenseManagement/Expenses/Index', [
            'expenses' => $this->expenses->paginate($request),
            'summary' => $this->expenses->listSummary($request),
            'filters' => $this->expenses->filters($request),
        ]);
    }

    public function create()
    {
        return Inertia::render('ExpenseManagement/Expenses/Create', [
            'warehouses' => Warehouse::query()->orderBy('name')->get(['id', 'name', 'status']),
            'suggestedExpenseNo' => $this->expenses->generateExpenseNo(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedExpense($request);

        Expense::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'expense_no' => $validated['expense_no'] ?: $this->expenses->generateExpenseNo(),
        ]));

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully');
    }

    public function show(string $id)
    {
        $expense = Expense::with(['warehouse', 'user'])->findOrFail($id);

        return Inertia::render('ExpenseManagement/Expenses/Show', [
            'expense' => $expense,
            'summary' => [
                'counts_toward_financials' => in_array($expense->status, ExpenseService::FINANCIAL_STATUSES, true),
            ],
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('ExpenseManagement/Expenses/Edit', [
            'expense' => Expense::findOrFail($id),
            'warehouses' => Warehouse::query()->orderBy('name')->get(['id', 'name', 'status']),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $expense = Expense::findOrFail($id);
        $validated = $this->validatedExpense($request, $expense->id);

        $expense->update($validated);

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully');
    }

    public function destroy(string $id)
    {
        Expense::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Expense deleted successfully');
    }

    protected function validatedExpense(Request $request, ?int $expenseId = null): array
    {
        $request->merge([
            'warehouse_id' => $request->filled('warehouse_id') ? (int) $request->input('warehouse_id') : null,
        ]);

        $validated = $request->validate($this->rules($expenseId));

        $validated['warehouse_id'] = ! empty($validated['warehouse_id'])
            ? (int) $validated['warehouse_id']
            : null;

        return $validated;
    }

    protected function rules(?int $expenseId = null): array
    {
        return [
            'expense_no' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expenses', 'expense_no')->ignore($expenseId),
            ],
            'expense_date' => 'required|date',
            'expense_name' => 'required|string|max:255',
            'warehouse_id' => 'nullable|integer|exists:warehouses,id',
            'employee_name' => 'nullable|string|max:255',
            'payee_name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank,cheque,online',
            'reference_no' => 'nullable|string|max:255',
            'invoice_no' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'remarks' => 'nullable|string',
            'status' => 'required|in:draft,approved,paid,cancelled',
        ];
    }
}
