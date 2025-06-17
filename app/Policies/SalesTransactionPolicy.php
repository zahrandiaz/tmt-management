<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Karung\Models\SalesTransaction;
use Illuminate\Auth\Access\Response;

class SalesTransactionPolicy
{
    // ... (method viewAny, view, create, update, delete, cancel tidak berubah) ...
    public function viewAny(User $user): bool
    {
        return $user->can('karung.view_sales');
    }

    public function view(User $user, SalesTransaction $salesTransaction): bool
    {
        return $user->can('karung.view_sales');
    }

    public function create(User $user): bool
    {
        return $user->can('karung.create_sales');
    }

    public function update(User $user, SalesTransaction $salesTransaction): bool
    {
        return $user->can('karung.edit_sales') && $salesTransaction->status === 'Completed';
    }

    public function delete(User $user, SalesTransaction $salesTransaction): bool
    {
        return $user->can('karung.delete_sales') && $salesTransaction->status === 'Completed';
    }

    public function cancel(User $user, SalesTransaction $salesTransaction): bool
    {
        return $user->can('karung.cancel_sales') && $salesTransaction->status === 'Completed';
    }

    /**
     * [BARU] Determine whether the user can restore the model.
     * Hanya Super Admin TMT yang bisa mengembalikan data dari 'sampah'.
     */
    public function restore(User $user, SalesTransaction $salesTransaction): bool
    {
        return $user->hasRole('Super Admin TMT');
    }

    public function managePayment(User $user, SalesTransaction $salesTransaction): bool
    {
        // Hanya izinkan jika pengguna punya permission DAN transaksi masih 'Completed'
        return $user->can('karung.manage_payments') && $salesTransaction->status === 'Completed';
    }

    public function forceDelete(User $user, SalesTransaction $salesTransaction): bool { return false; }
}