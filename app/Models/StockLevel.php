<?php

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;

class StockLevel extends Model
{

    use BelongsToTenant, Filterable;

    protected $fillable = ['product_id', 'warehouse_id', 'quantity'];
    protected $casts = ['quantity' => 'integer'];
}
