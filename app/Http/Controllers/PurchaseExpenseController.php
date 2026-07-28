<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PurchaseExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = PurchaseExpense::query()
            ->with(['purchase', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('expense_type', 'like', "%{$search}%")
                        ->orWhereHas('purchase', fn ($p) => $p->where('purchase_no', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('purchase_id'), function ($query) use ($request) {
                $query->where('purchase_id', $request->purchase_id);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('InventoryManagement/PurchaseExpenses/Index', [
            'expenses' => $expenses,
            'purchases' => Purchase::select('id', 'purchase_no')->get(),
            'filters' => $request->only('search', 'purchase_id'),
        ]);
    }

    public function create()
    {
        return Inertia::render('InventoryManagement/PurchaseExpenses/Create', [
            'purchases' => Purchase::select('id', 'purchase_no')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'expense_type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        PurchaseExpense::create(array_merge($request->all(), ['user_id' => Auth::id()]));

        return redirect()->route('purchase-expenses.index')->with('success', 'Purchase expense recorded');
    }

    public function show(string $id)
    {
        return Inertia::render('InventoryManagement/PurchaseExpenses/Show', [
            'expense' => PurchaseExpense::with(['purchase', 'user'])->findOrFail($id),
        ]);
    }

    public function edit(string $id)
    {
        return Inertia::render('InventoryManagement/PurchaseExpenses/Edit', [
            'expense' => PurchaseExpense::findOrFail($id),
            'purchases' => Purchase::select('id', 'purchase_no')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $expense = PurchaseExpense::findOrFail($id);

        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'expense_type' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => 'boolean',
        ]);

        $expense->update($request->all());

        return redirect()->route('purchase-expenses.index')->with('success', 'Purchase expense updated');
    }

    public function destroy(string $id)
    {
        PurchaseExpense::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Purchase expense deleted');
    }
}
