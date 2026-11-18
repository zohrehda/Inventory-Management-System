<?php

namespace App\Http\Controllers;

use App\Http\Resources\StockMovementResource;
use App\Models\Product;
use App\Models\StockMovement;

class ProductController extends Controller
{
    public function historyBySKU(Product $product)
    {
        return   StockMovementResource::collection(StockMovement::where('product_id', $product->id)->paginate());
    }
}
