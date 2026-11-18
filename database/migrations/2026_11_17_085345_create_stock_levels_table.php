<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('quantity')->default(0);
            $table->timestamps();
            $table->unique(['tenant_id', 'product_id', 'warehouse_id'], 'sl_tenant_product_warehouse_uq');
            $table->index(['tenant_id', 'id'], 'sl_tenant_id_idx');
            $table->index(['tenant_id', 'product_id'], 'sl_tenant_product_idx');
            $table->index(['tenant_id', 'warehouse_id'], 'sl_tenant_warehouse_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_levels');
    }
};
