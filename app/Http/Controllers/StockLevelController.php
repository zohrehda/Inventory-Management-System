<?php

namespace App\Http\Controllers;

use App\Http\Resources\StockLevelResource;
use App\Models\StockLevel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockLevelController extends Controller
{
    public function index() :AnonymousResourceCollection
    {
        return StockLevelResource::collection(StockLevel::filter()->paginate());
    }
}
