<?php

namespace Tests\Unit\Modules\Karung;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\Karung\Services\StockManagementService;
use App\Modules\Karung\Models\Product;
use App\Models\Setting;
use App\Modules\Karung\Models\ProductCategory;
use App\Modules\Karung\Models\ProductType;
use App\Modules\Karung\Models\SalesTransaction;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Models\User;
use App\Modules\Karung\Models\Customer;
use App\Modules\Karung\Models\Supplier;
use PHPUnit\Framework\Attributes\Test;
use Carbon\Carbon;

class StockManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;
    private StockManagementService $stockService;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::create(['business_unit_id' => 1, 'setting_key' => 'automatic_stock_management', 'setting_value' => 'true']);
        $category = ProductCategory::create(['name' => 'Umum', 'business_unit_id' => 1]);
        $type = ProductType::create(['name' => 'Karung', 'business_unit_id' => 1]);
        
        $this->product = Product::create([
            'name' => 'Karung 50kg', 'business_unit_id' => 1, 'product_category_id' => $category->id,
            'product_type_id' => $type->id, 'sku' => 'KR001', 'purchase_price' => 1000,
            'selling_price' => 1500, 'stock' => 100, 'is_active' => true,
        ]);

        $this->stockService = new StockManagementService();
    }

    #[Test]
    public function it_decreases_stock_when_a_sale_is_created(): void
    {
        $this->stockService->handleSaleCreation([['product_id' => $this->product->id, 'quantity' => 10]]);
        $this->assertEquals(90, $this->product->fresh()->stock);
    }

    #[Test]
    public function it_increases_stock_when_a_purchase_is_created(): void
    {
        $this->stockService->handlePurchaseCreation([['product_id' => $this->product->id, 'quantity' => 25]]);
        $this->assertEquals(125, $this->product->fresh()->stock);
    }

    #[Test]
    public function it_restores_stock_when_a_sale_is_cancelled(): void
    {
        $salesTransaction = $this->createDummySale(10);
        $this->stockService->handleSaleCreation($salesTransaction->details->toArray());
        $this->assertEquals(90, $this->product->fresh()->stock);

        $this->stockService->handleSaleCancellation($salesTransaction);
        
        $this->assertEquals(100, $this->product->fresh()->stock);
    }

    #[Test]
    public function it_reverts_stock_when_a_purchase_is_cancelled(): void
    {
        $purchaseTransaction = $this->createDummyPurchase(25);
        $this->stockService->handlePurchaseCreation($purchaseTransaction->details->toArray());
        $this->assertEquals(125, $this->product->fresh()->stock);

        $this->stockService->handlePurchaseCancellation($purchaseTransaction);

        $this->assertEquals(100, $this->product->fresh()->stock);
    }

    /*
    |--------------------------------------------------------------------------
    | TES FINAL DITAMBAHKAN DI SINI (UPDATE)
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function it_adjusts_stock_correctly_when_a_sale_is_updated(): void
    {
        // ARRANGE: Stok awal 100. Buat penjualan awal 10 item -> stok jadi 90
        $originalSale = $this->createDummySale(10);
        $this->stockService->handleSaleCreation($originalSale->details->toArray());
        $this->assertEquals(90, $this->product->fresh()->stock);
        
        // Data baru untuk update, kuantitas berubah dari 10 menjadi 15
        $newDetails = [['product_id' => $this->product->id, 'quantity' => 15]];

        // ACT: Panggil method update
        $this->stockService->handleSaleUpdate($originalSale, $newDetails);

        // ASSERT: Stok akhir harus 85 (100 - 15)
        $this->assertEquals(85, $this->product->fresh()->stock);
    }

    #[Test]
    public function it_adjusts_stock_correctly_when_a_purchase_is_updated(): void
    {
        // ARRANGE: Stok awal 100. Buat pembelian awal 20 item -> stok jadi 120
        $originalPurchase = $this->createDummyPurchase(20);
        $this->stockService->handlePurchaseCreation($originalPurchase->details->toArray());
        $this->assertEquals(120, $this->product->fresh()->stock);

        // Data baru untuk update, kuantitas berubah dari 20 menjadi hanya 5
        $newDetails = [['product_id' => $this->product->id, 'quantity' => 5]];

        // ACT: Panggil method update
        $this->stockService->handlePurchaseUpdate($originalPurchase, $newDetails);

        // ASSERT: Stok akhir harus 105 (100 + 5)
        $this->assertEquals(105, $this->product->fresh()->stock);
    }


    /*
    |--------------------------------------------------------------------------
    | Helper Methods untuk membuat data dummy
    |--------------------------------------------------------------------------
    */
    private function createDummySale(int $quantity): SalesTransaction
    {
        $customer = Customer::firstOrCreate(['name' => 'Pelanggan Uji', 'business_unit_id' => 1]);
        $user = User::factory()->create();
        $salesTransaction = SalesTransaction::create([
            'business_unit_id' => 1, 'invoice_number' => 'SALE-DUMMY', 'customer_id' => $customer->id,
            'user_id' => $user->id, 'total_amount' => 1, 'transaction_date' => Carbon::now(),
        ]);
        $salesTransaction->details()->create([
            'product_id' => $this->product->id, 'quantity' => $quantity,
            'selling_price_at_transaction' => 1, 'sub_total' => 1,
        ]);
        return $salesTransaction;
    }

    private function createDummyPurchase(int $quantity): PurchaseTransaction
    {
        $supplier = Supplier::firstOrCreate(['name' => 'Supplier Uji', 'business_unit_id' => 1]);
        $user = User::factory()->create();
        $purchaseTransaction = PurchaseTransaction::create([
            'business_unit_id' => 1, 'purchase_code' => 'PUR-DUMMY', 'supplier_id' => $supplier->id,
            'user_id' => $user->id, 'total_amount' => 1, 'transaction_date' => Carbon::now(),
        ]);
        $purchaseTransaction->details()->create([
            'product_id' => $this->product->id, 'quantity' => $quantity,
            'purchase_price_at_transaction' => 1, 'sub_total' => 1,
        ]);
        return $purchaseTransaction;
    }
}