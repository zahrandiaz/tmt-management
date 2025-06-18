<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Karung\Models\OperationalExpense;
use Illuminate\Auth\Access\Response;

class OperationalExpensePolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat semua data.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('karung.manage_expenses');
    }

    /**
     * Tentukan apakah pengguna dapat melihat satu data.
     */
    public function view(User $user, OperationalExpense $operationalExpense): bool
    {
        return $user->can('karung.manage_expenses');
    }

    /**
     * Tentukan apakah pengguna dapat membuat data.
     */
    public function create(User $user): bool
    {
        return $user->can('karung.manage_expenses');
    }

    /**
     * Tentukan apakah pengguna dapat memperbarui data.
     */
    public function update(User $user, OperationalExpense $operationalExpense): bool
    {
        return $user->can('karung.manage_expenses');
    }

    /**
     * Tentukan apakah pengguna dapat menghapus data.
     */
    public function delete(User $user, OperationalExpense $operationalExpense): bool
    {
        return $user->can('karung.manage_expenses');
    }

    /**
     * Tentukan apakah pengguna dapat memulihkan data.
     */
    public function restore(User $user, OperationalExpense $operationalExpense): bool
    {
        return $user->can('karung.manage_expenses');
    }

    /**
     * Tentukan apakah pengguna dapat menghapus data secara permanen.
     */
    public function forceDelete(User $user, OperationalExpense $operationalExpense): bool
    {
        return $user->can('karung.manage_expenses');
    }
}