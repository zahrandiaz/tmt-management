<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

// Import Model dan Policy yang akan kita daftarkan
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Policies\PurchaseTransactionPolicy;
use App\Modules\Karung\Models\SalesTransaction; // <-- Tambah ini
use App\Policies\SalesTransactionPolicy;      // <-- Tambah ini
use App\Modules\Karung\Models\OperationalExpense;
use App\Policies\OperationalExpensePolicy;
use App\Modules\Karung\Models\Product;
use App\Policies\ProductPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Daftarkan mapping di sini
        PurchaseTransaction::class => PurchaseTransactionPolicy::class,
        SalesTransaction::class => SalesTransactionPolicy::class,
        OperationalExpense::class => OperationalExpensePolicy::class,
        Product::class => ProductPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}