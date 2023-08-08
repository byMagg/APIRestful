<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Buyer;
use Illuminate\Http\Request;

class BuyerSellerController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Buyer $buyer)
    {
        $sellers = $buyer->transactions()->with("product.seller")
            ->get()
            ->pluck("product.seller")
            ->unique();

        dd($sellers);

        return $this->showAll($sellers);
    }
}
