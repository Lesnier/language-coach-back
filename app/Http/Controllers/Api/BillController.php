<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function index()
    {
        $bills = Bill::with('user','subscription','payment','products')->get();
        return response()->json(
            ['bills' => $bills]
        );
    }

    public function show($id)
    {
        $bill = Bill::with('user','subscription.products','payment','products')->find($id);

        if (!$bill) {
            return response()->json(['error' => 'Bill not found'], 404);
        }

        return response()->json($bill);
    }

}
