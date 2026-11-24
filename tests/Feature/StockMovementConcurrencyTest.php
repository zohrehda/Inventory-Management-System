<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockMovementService;
use App\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Concurrency;
use Tests\TestCase;
use Throwable;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class StockMovementConcurrencyTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     */
    public function test_one_hundred_parallel_out_movements_leave_exactly_zero(): void
    {
        $tenant = Tenant::factory()->create();

        $product = Product::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $warehouse = Warehouse::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $tenantId = $tenant->id;
        $productId = $product->id;
        $warehouseId = $warehouse->id;

        tenancy()->run($tenant, function () use ($productId, $warehouseId) {
            app(StockMovementService::class)->record([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'type' => 'in',
                'quantity' => 50,
            ]);
        });

        $tasks = [];

        for ($i = 0; $i < 50; $i++) {
            $tasks[] = static function () use ($tenantId, $productId, $warehouseId) {
                try {
                    $tenant = Tenant::findOrFail($tenantId);
                    tenancy()->run($tenant, function () use ($productId, $warehouseId) {
                        app(StockMovementService::class)->record([
                            'product_id' => $productId,
                            'warehouse_id' => $warehouseId,
                            'type' => 'out',
                            'quantity' => 1,
                        ]);
                    });

                    return 'ok';
                } catch (InsufficientStockException $e) {

                    return 'insufficient';
                } catch (Throwable $e) {
                    return 'error: ' . $e::class . ': ' . $e->getMessage();
                }
            };
        }

        $results = array_count_values(Concurrency::run($tasks));

        $this->assertSame(50, $results['ok']);
        $this->assertSame(50, $results['insufficient']);

        tenancy()->run($tenant, function () use($productId,$warehouseId) {
            $this->assertSame(0, StockLevel::where('product_id', $productId)->where('warehouse_id', $warehouseId)->value('quantity'));
        //    $this->assertSame(50, StockMovement::where('type', 'out')->count());
        });
   
    }
}
