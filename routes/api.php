<?php


use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockLevelController;
use App\Http\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::middleware('tenant')->group(function () {
    Route::post('stock-movements', [StockMovementController::class, 'store']);
    Route::get('stock-levels', [StockLevelController::class, 'index']);
    Route::get('products/{product:sku}/history', [ProductController::class, 'historyBySKU']);
});
