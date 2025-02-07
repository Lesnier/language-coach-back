<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getPrices(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:products,id',
        ]);

        // Obtener los precios de los productos seleccionados
        $prices = Product::whereIn('id', $request->ids)
            ->pluck('total')
            ->toArray();

        // Devolver los precios en formato JSON
        return response()->json([
            'prices' => $prices,
        ]);
    }

}
