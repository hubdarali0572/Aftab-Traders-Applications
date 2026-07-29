<?php

namespace App\Http\Controllers;

use App\Models\PurchaseExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseExpenseController extends Controller
{
    public function index()
    {
        return redirect()->route('purchases.index');
    }

    public function create()
    {
        return redirect()->route('purchases.index');
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

        return redirect()->route('purchases.show', $request->purchase_id)->with('success', 'Purchase expense recorded');
    }

    public function show(string $id)
    {
        $expense = PurchaseExpense::findOrFail($id);

        return redirect()->route('purchases.show', $expense->purchase_id);
    }

    public function edit(string $id)
    {
        $expense = PurchaseExpense::findOrFail($id);

        return redirect()->route('purchases.show', $expense->purchase_id);
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

        return redirect()->route('purchases.show', $expense->purchase_id)->with('success', 'Purchase expense updated');
    }

    public function destroy(string $id)
    {
        $expense = PurchaseExpense::findOrFail($id);
        $purchaseId = $expense->purchase_id;
        $expense->delete();

        return redirect()->route('purchases.show', $purchaseId)->with('success', 'Purchase expense deleted');
    }
}
