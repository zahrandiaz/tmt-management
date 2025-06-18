<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Karung\Models\OperationalExpense;

class OperationalExpenseController extends Controller
{
    // Kategori biaya yang telah ditentukan
    private $expenseCategories = ['Gaji', 'Sewa', 'Listrik/Air', 'Internet', 'Pemasaran', 'Transportasi', 'Lain-lain'];

    public function index(Request $request)
    {
        $this->authorize('viewAny', OperationalExpense::class);

        $query = OperationalExpense::with('user');

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $expenses = $query->latest('date')->paginate(20);

        return view('karung::operational_expenses.index', compact('expenses'));
    }

    public function create()
    {
        $this->authorize('create', OperationalExpense::class);
        $categories = $this->expenseCategories;
        return view('karung::operational_expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', OperationalExpense::class);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'category' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['business_unit_id'] = 1; // Hardcode untuk V1
        $validated['user_id'] = auth()->id();

        OperationalExpense::create($validated);

        return redirect()->route('karung.operational-expenses.index')
                         ->with('success', 'Biaya operasional berhasil ditambahkan.');
    }

    public function edit(OperationalExpense $operationalExpense)
    {
        $this->authorize('update', $operationalExpense);
        $categories = $this->expenseCategories;
        return view('karung::operational_expenses.edit', compact('operationalExpense', 'categories'));
    }

    public function update(Request $request, OperationalExpense $operationalExpense)
    {
        $this->authorize('update', $operationalExpense);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'category' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $operationalExpense->update($validated);

        return redirect()->route('karung.operational-expenses.index')
                         ->with('success', 'Biaya operasional berhasil diperbarui.');
    }

    public function destroy(OperationalExpense $operationalExpense)
    {
        $this->authorize('delete', $operationalExpense);
        $operationalExpense->delete();
        return redirect()->route('karung.operational-expenses.index')
                         ->with('success', 'Biaya operasional berhasil dihapus.');
    }
}