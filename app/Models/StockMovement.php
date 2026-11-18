<?php

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{

    use BelongsToTenant;
    
    protected $fillable = ['product_id', 'warehouse_id', 'type', 'quantity', 'reference', 'meta'];

    protected $casts = ['quantity' => 'integer', 'meta' => 'array'];
}
