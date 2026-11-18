<?php

namespace App\Http\Controllers;

use App\Http\Resources\StockLevelResource;
use App\Models\StockLevel;

class StockLevelController extends Controller
{
    public function index()
    {
        return StockLevelResource::collection(StockLevel::filter()->paginate());
    }
}
