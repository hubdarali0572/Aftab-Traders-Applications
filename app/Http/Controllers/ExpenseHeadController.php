<?php

namespace App\Http\Controllers;

use App\Models\ExpenseHead;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ExpenseHeadController extends Controller
{
    public function index(Request $request)
    {
        $expenseHeads = ExpenseHead::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('head_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('ExpenseManagement/ExpenseHeads/Index', [
            'expenseHeads' => $expenseHeads,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('ExpenseManagement/ExpenseHeads/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'head_code' => 'required|string|max:255|unique:expense_heads,head_code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        ExpenseHead::create([
            'head_code' => $request->head_code,
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status ?? true,
        ]);

        return redirect()->route('expense-heads.index')->with('success', 'Expense head created successfully');
    }

    public function show(string $id)
    {
        return Inertia::render('ExpenseManagement/ExpenseHeads/Show', [
            'expenseHead' => ExpenseHead::withCount('expenses')->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('ExpenseManagement/ExpenseHeads/Edit', [
            'expenseHead' => ExpenseHead::findOrFail($id),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $expenseHead = ExpenseHead::findOrFail($id);

        $request->validate([
            'head_code' => ['required', 'string', 'max:255', Rule::unique('expense_heads', 'head_code')->ignore($expenseHead->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $expenseHead->update([
            'head_code' => $request->head_code,
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status ?? true,
        ]);

        return redirect()->route('expense-heads.index')->with('success', 'Expense head updated successfully');
    }

    public function destroy(string $id)
    {
        ExpenseHead::findOrFail($id)->delete();

        return redirect()->route('expense-heads.index')->with('success', 'Expense head deleted successfully');
    }
}
