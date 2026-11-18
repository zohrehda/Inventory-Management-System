<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\StockLevel;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    public function record(array $data)
    {
        $tenantId = tenant()->id;
        DB::transaction(function () use ($data, $tenantId) {

            match ($data['type']) {
                StockMovementType::IN->value => $this->applyIn($tenantId, $data),
                StockMovementType::OUT->value => $this->applyOut($tenantId, $data),
                StockMovementType::TRANSFER->value => $this->applyTransfer($tenantId, $data),
            };
        }, 5);
    }

    private function ensureStockLevel(int $tenantId, int $productId, array $warehouseIds)
    {
        sort($warehouseIds);

        $select = fn() => StockLevel::query()
            ->where('product_id', $productId)
            ->whereIn('warehouse_id', $warehouseIds)
            ->orderBy('warehouse_id')
            ->lockForUpdate()
            ->get()
            ->keyBy('warehouse_id');

        $levels = $select();
        //  $missing = array_values(array_diff($createMissing, $levels->keys()->all()));
        $missing = array_diff($warehouseIds, $levels->keys()->all());

        // print_r($missing);
        //return;

        if ($missing !== []) {

            DB::table('stock_levels')->insertOrIgnore(array_map(fn(int $warehouseId) => [
                'tenant_id' => $tenantId,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => 0,
            ], $missing));

            $levels = $select(); // re-lock (already-held locks are fine) and pick up the new rows
        }

        return $levels;
    }


    private function applyIn(int $tenantId, array $data)
    {
        $levels =  $this->ensureStockLevel($tenantId, $data['product_id'], [$data['warehouse_id']]);
        $stockLevel = $levels->get($data['warehouse_id']);
        StockMovement::create($data);
        $stockLevel->increment('quantity', $data['quantity']);
    }

    private function applyOut(int $tenantId, array $data)
    {
        $levels = $this->ensureStockLevel($tenantId, $data['product_id'], [$data['warehouse_id']]);

        $stockLevel = $levels->get($data['warehouse_id']);
        if ($stockLevel->quantity < $data['quantity'])
            throw new InsufficientStockException();

        StockMovement::create($data);
        $stockLevel->decrement('quantity', $data['quantity']);
    }

    private function applyTransfer(int $tenantId, array $data)
    {
        $levels =  $this->ensureStockLevel($tenantId, $data['product_id'], [$data['warehouse_id'], $data['to_warehouse_id']]);
        $source = $levels->get($data['warehouse_id']);
        $destination = $levels->get($data['to_warehouse_id']);

        if ($source->quantity < $data['quantity'])
            throw new InsufficientStockException();

        StockMovement::create($data + [
            'meta' => [
                'from_warehouse_id' => $data['warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
            ]
        ]);
        $source->decrement('quantity', $data['quantity']);
        $destination->increment('quantity', $data['quantity']);
    }
}
