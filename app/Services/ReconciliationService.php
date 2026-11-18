<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReconciliationService
{

    private const EXPECTED_SQL = "
        SELECT product_id, warehouse_id, SUM(delta) AS qty FROM (
            SELECT product_id, warehouse_id,
                   CASE type WHEN 'in' THEN CAST(quantity AS SIGNED) ELSE -CAST(quantity AS SIGNED) END AS delta
            FROM stock_movements WHERE tenant_id = ?
            UNION ALL
            SELECT product_id,
                   CAST(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.to_warehouse_id')) AS UNSIGNED) AS warehouse_id,
                   CAST(quantity AS SIGNED) AS delta
            FROM stock_movements WHERE tenant_id = ? AND type = 'transfer'
        ) movements
        GROUP BY product_id, warehouse_id";

    public function discrepancies(int $tenantId)
    {

        $compareMovement = '
            SELECT e.product_id, e.warehouse_id, e.qty AS expected, COALESCE(sl.quantity, 0) AS actual
            FROM (' . self::EXPECTED_SQL . ') e
            LEFT JOIN stock_levels sl
              ON sl.tenant_id = ? AND sl.product_id = e.product_id AND sl.warehouse_id = e.warehouse_id
            WHERE COALESCE(sl.quantity, 0) <> e.qty';


        $compareLevel = '
            SELECT sl.product_id, sl.warehouse_id, 0 AS expected, sl.quantity AS actual
            FROM stock_levels sl
            LEFT JOIN (' . self::EXPECTED_SQL . ') e
              ON e.product_id = sl.product_id AND e.warehouse_id = sl.warehouse_id
            WHERE sl.tenant_id = ? AND sl.quantity <> 0 AND e.product_id IS NULL';


     return   DB::transaction(function () use ($compareMovement, $compareLevel, $tenantId) {

            foreach ([$compareMovement, $compareLevel] as $sql) {
                foreach (DB::cursor($sql, [$tenantId, $tenantId, $tenantId]) as $row) {
                    yield [
                        'product_id' => (int) $row->product_id,
                        'warehouse_id' => (int) $row->warehouse_id,
                        'expected' => (int) $row->expected,
                        'actual' => (int) $row->actual,
                    ];
                }
            }
        });
    }
}
