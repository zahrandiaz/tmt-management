<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Karung\Models\SalesTransaction;
use Illuminate\Auth\Access\Response;

class SalesTransactionPolicy
{
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

    // ... sisa method (restore, forceDelete) bisa dibiarkan
}