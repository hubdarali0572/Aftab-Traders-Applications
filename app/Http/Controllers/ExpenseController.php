<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseHead;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::query()
            ->with(['expenseHead', 'warehouse', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('expense_no', 'like', "%{$search}%")
                        ->orWhere('payee_name', 'like', "%{$search}%")
                        ->orWhere('employee_name', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%")
                        ->orWhere('invoice_no', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('expenseHead', function ($h) use ($search) {
                            $h->where('name', 'like', "%{$search}%")
                                ->orWhere('head_code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('expense_head_id'), function ($query) use ($request) {
                $query->where('expense_head_id', $request->expense_head_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('payment_method'), function ($query) use ($request) {
                $query->where('payment_method', $request->payment_method);
            })
            ->latest('expense_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('ExpenseManagement/Expenses/Index', [
            'expenses' => $expenses,
            'expenseHeads' => ExpenseHead::where('status', true)->orderBy('name')->get(['id', 'head_code', 'name']),
            'filters' => $request->only('search', 'expense_head_id', 'status', 'payment_method'),
        ]);
    }

    public function create()
    {
        return Inertia::render('ExpenseManagement/Expenses/Create', [
            'expenseHeads' => ExpenseHead::where('status', true)->orderBy('name')->get(['id', 'head_code', 'name']),
            'warehouses' => Warehouse::query()->orderBy('name')->get(['id', 'name', 'status']),
            'suggestedExpenseNo' => $this->nextExpenseNo(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedExpense($request);

        Expense::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'expense_no' => $validated['expense_no'] ?: $this->nextExpenseNo(),
        ]));

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('ExpenseManagement/Expenses/Show', [
            'expense' => Expense::with(['expenseHead', 'warehouse', 'user'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('ExpenseManagement/Expenses/Edit', [
            'expense' => Expense::findOrFail($id),
            'expenseHeads' => ExpenseHead::orderBy('name')->get(['id', 'head_code', 'name']),
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
            'expense_head_id' => $request->filled('expense_head_id') ? (int) $request->input('expense_head_id') : null,
            'warehouse_id' => $request->filled('warehouse_id') ? (int) $request->input('warehouse_id') : null,
        ]);

        $validated = $request->validate($this->rules($expenseId));

        $validated['warehouse_id'] = ! empty($validated['warehouse_id'])
            ? (int) $validated['warehouse_id']
            : null;
        $validated['expense_head_id'] = (int) $validated['expense_head_id'];

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
            'expense_head_id' => 'required|integer|exists:expense_heads,id',
            'warehouse_id' => 'required|integer|exists:warehouses,id',
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

    protected function nextExpenseNo(): string
    {
        $prefix = 'EXP-' . now()->format('Ymd') . '-';
        $latest = Expense::withTrashed()
            ->where('expense_no', 'like', $prefix . '%')
            ->orderByDesc('expense_no')
            ->value('expense_no');

        $sequence = 1;
        if ($latest && preg_match('/(\d+)$/', $latest, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
