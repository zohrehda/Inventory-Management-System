<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockMovementRequest;
use App\Services\StockMovementService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


class StockMovementController extends Controller
{
    public function store(StoreStockMovementRequest $storeStockMovementRequest, StockMovementService $stockMovementService)
    {
        $stockMovementService->record($storeStockMovementRequest->validated());
    }
}
