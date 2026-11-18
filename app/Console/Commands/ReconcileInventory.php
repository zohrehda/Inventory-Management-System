<?php

namespace App\Console\Commands;

use App\Services\ReconciliationService;
use App\Tenancy\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcileInventory extends Command
{
    protected $signature = 'inventory:reconcile {tenant : Tenant ID}';
    protected $description = 'Reconcile inventory';

    /**
     * Execute the console command.
     */
    public function handle(ReconciliationService $reconciliationService)
    {
        $subDomain = $this->argument('tenant');

        $tenant = Tenant::where('subdomain', $subDomain)->first();

        if (!$tenant) {
            $this->error("Tenant {$subDomain} not found.");
            return self::FAILURE;
        }

        $count = 0;
        foreach ($reconciliationService->discrepancies($tenant->id) as $row) {
            $count++;
            Log::channel('inventory_reconcile')->warning('inventory.reconcile.disdcrepancy', ['tenant_id' => $tenant->id] + $row);
            $this->warn(sprintf(
                'product=%d warehouse=%d expected=%d actual=%d',
                $row['product_id'],
                $row['warehouse_id'],
                $row['expected'],
                $row['actual']
            ));
        }

        if ($count === 0) {
            $this->info(trans('messages.inventory_reconcile_match', ['subdomain' => $subDomain]));
            return self::SUCCESS;
        }
        $this->error(trans('messages.inventory_reconcile_disdcrepancy', ['count' => $count, 'subdomain' => $subDomain]));
        return self::FAILURE;
    }
}
