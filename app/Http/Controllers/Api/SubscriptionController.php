<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function getPrice($id)
    {
        $susbcription = Subscription::with('products')->find($id);

        if (!$susbcription) {
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        // Calcular el precio total de la suscripción
        $subscriptionPrice = $susbcription->products->sum(function ($product) {
            return $product->total;
        });

        // Devolver los datos en formato JSON
        return response()->json([
            'subscription_products' => $susbcription->products,
            'total' => $subscriptionPrice,
        ]);
    }

}
