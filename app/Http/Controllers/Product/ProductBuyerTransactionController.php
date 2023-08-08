<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Transformers\TransactionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductBuyerTransactionController extends ApiController
{

    public function __construct()
    {
        parent::__construct();

        $this->middleware('transform.input:' . TransactionTransformer::class)->only(['store']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Product $product, User $buyer)
    {
        if ($buyer->id == $product->seller_id) {
            return $this->errorResponse("El comprador debe ser diferente al vendedor", 409);
        }
        if (!$buyer->esVerificado()) {
            return $this->errorResponse("El comprador debe ser un usuario verificado", 409);
        }
        if (!$product->seller->esVerificado()) {
            return $this->errorResponse("El vendedor debe ser un usuario verificado", 409);
        }
        if (!$product->estaDisponible()) {
            return $this->errorResponse("El producto para esta transacción no está disponible", 409);
        }
        if ($product->quantity < $request->quantity) {
            return $this->errorResponse("El producto no tiene la cantidad disponible requerida para esta transacción", 409);
        }

        return DB::transaction(function () use ($request, $product, $buyer) {
            $product->quantity -= $request->quantity;
            $product->save();

            $transaction = Transaction::create([
                "quantity" => $request->quantity,
                "buyer_id" => $buyer->id,
                "product_id" => $product->id,
            ]);

            return $this->showOne($transaction, 201);
        });
    }
}