<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function index()
    {
        $bills = Bill::with('user','subscription','payment','products')->get();
        return response()->json(
            ['Bills' => $bills]
        );
    }
}
