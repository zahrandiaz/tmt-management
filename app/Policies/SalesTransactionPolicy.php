<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model; // <-- UBAH INI
use Illuminate\Auth\Access\Response;

class SalesTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('karung.view_sales');
    }

    // Ganti 'SalesTransaction' menjadi 'Model' di semua method di bawah
    public function view(User $user, Model $salesTransaction): bool
    {
        return $user->can('karung.view_sales');
    }

    public function create(User $user): bool
    {
        return $user->can('karung.create_sales');
    }

    public function update(User $user, Model $salesTransaction): bool
    {
        return $user->can('karung.edit_sales') && $salesTransaction->status === 'Completed';
    }

    public function delete(User $user, Model $salesTransaction): bool
    {
        return $user->can('karung.delete_sales') && $salesTransaction->status === 'Completed';
    }

    public function cancel(User $user, Model $salesTransaction): bool
    {
        return $user->can('karung.cancel_sales') && $salesTransaction->status === 'Completed';
    }

    public function restore(User $user, Model $salesTransaction): bool
    {
        return $user->hasRole('Super Admin TMT');
    }

    public function managePayment(User $user, Model $salesTransaction): bool
    {
        return $user->can('karung.manage_payments') && $salesTransaction->status === 'Completed';
    }

    public function forceDelete(User $user, Model $salesTransaction): bool { return false; }
}