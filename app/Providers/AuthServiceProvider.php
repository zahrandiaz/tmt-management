<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

// Import Model dan Policy yang akan kita daftarkan
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Policies\PurchaseTransactionPolicy;
use App\Modules\Karung\Models\SalesTransaction;
use App\Policies\SalesTransactionPolicy;
use App\Modules\Karung\Models\OperationalExpense;
use App\Policies\OperationalExpensePolicy;
use App\Modules\Karung\Models\Product;
use App\Policies\ProductPolicy;

// [BARU v1.27] Import model dan policy untuk retur
use App\Modules\Karung\Models\SalesReturn;
use App\Policies\SalesReturnPolicy;
use App\Modules\Karung\Models\PurchaseReturn;
use App\Policies\PurchaseReturnPolicy;

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

        // [BARU v1.27] Daftarkan policy untuk retur
        SalesReturn::class => SalesReturnPolicy::class,
        PurchaseReturn::class => PurchaseReturnPolicy::class,
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