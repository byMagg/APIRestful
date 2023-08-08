<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Buyer;
use Illuminate\Http\Request;

class BuyerProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Buyer $buyer)
    {
        $products = $buyer->transactions()->with('product')
            ->get()
            ->pluck();

        dd($products);

        return $this->showAll($products);
    }
}
