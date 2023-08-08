<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\ApiController;
use App\Models\Seller;

class SellerBuyerController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Seller $seller)
    {
        $buyers = $seller->products()
            ->whereHas('transactions')
            ->with('transactions.buyers')
            ->get()
            ->pluck('transactions')
            ->collapse()
            ->pluck('buyer')
            ->unique()
            ->values();

        return $this->showAll($buyers);
    }
}
