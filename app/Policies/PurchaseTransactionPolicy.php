<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Karung\Models\PurchaseTransaction;
use Illuminate\Auth\Access\Response;

class PurchaseTransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('karung.view_purchases');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PurchaseTransaction $purchaseTransaction): bool
    {
        return $user->can('karung.view_purchases');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('karung.create_purchases');
    }

    /**
     * Determine whether the user can update the model.
     * Aturan: Pengguna harus punya izin 'edit' DAN status transaksi harus 'Completed'.
     */
    public function update(User $user, PurchaseTransaction $purchaseTransaction): bool
    {
        return $user->can('karung.edit_purchases') && $purchaseTransaction->status === 'Completed';
    }

    /**
     * Determine whether the user can delete the model.
     * Aturan: Pengguna harus punya izin 'delete' DAN status transaksi harus 'Completed'.
     */
    public function delete(User $user, PurchaseTransaction $purchaseTransaction): bool
    {
        return $user->can('karung.delete_purchases') && $purchaseTransaction->status === 'Completed';
    }

    /**
     * Determine whether the user can cancel the model.
     * Aturan: Pengguna harus punya izin 'cancel' DAN status transaksi harus 'Completed'.
     */
    public function cancel(User $user, PurchaseTransaction $purchaseTransaction): bool
    {
        return $user->can('karung.cancel_purchases') && $purchaseTransaction->status === 'Completed';
    }

    /**
     * Determine whether the user can restore the model.
     * (Kita siapkan untuk masa depan, saat ini tidak digunakan)
     */
    public function restore(User $user, PurchaseTransaction $purchaseTransaction): bool
    {
        // Contoh: return $user->can('karung.restore_purchases');
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     * (Kita siapkan untuk masa depan, saat ini tidak digunakan)
     */
    public function forceDelete(User $user, PurchaseTransaction $purchaseTransaction): bool
    {
        // Contoh: return $user->can('karung.force_delete_purchases');
        return false;
    }
}
