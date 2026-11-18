<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockMovementRequest;
use App\Services\StockMovementService;


class StockMovementController extends Controller
{
    public function store(StoreStockMovementRequest $storeStockMovementRequest, StockMovementService $stockMovementService)
    {
        $stockMovementService->record($storeStockMovementRequest->validated());
    }
}
