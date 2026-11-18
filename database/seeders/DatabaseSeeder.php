<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Tenancy\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        $tenant = Tenant::factory()->create();

        Product::factory()->create([
            'tenant_id' => $tenant->id
        ]);
        Warehouse::factory()
            ->count(2)
            ->create([
                'tenant_id' => $tenant->id
            ]);
    }
}
