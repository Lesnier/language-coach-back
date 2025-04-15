<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $user = auth()->user(); // Get the authenticated user
        $payments = Payment::with('user')->where('user_id', $user->id)->get(); // Filter by user_id
        return response()->json($payments);
    }

    public function show($id)
    {
        $payment = Payment::find($id);
        
        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        return response()->json($payment);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'transaction_code' => 'required',
            'image' => 'required'
        ]);

        $payment = new Payment();
        $payment->user_id = auth()->id(); // Use authenticated user's ID
        $payment->transaction_code = $validatedData['transaction_code'];

        if ($request->hasFile('image'))
        {
            $fileAdd = $request->file('image');
            $newFile = $this->storeFile($fileAdd);
            $payment->image = $newFile;
        }

        $payment->save();

        return response()->json([
            'message' => 'Payment created',
            'Payment' => $payment
        ], 201);
    }

    private function storeFile($file)
    {
        $filename = $file->getClientOriginalName();
        $filename = pathinfo($filename,PATHINFO_FILENAME);
        $name_file = str_replace(" ","_",$filename);
        $extension = $file->getClientOriginalExtension();
        $month_year = Carbon::now()->monthName . Carbon::now()->year;
        $final_name = date("His") . "_" . $name_file . "." . $extension;
        $file->move(storage_path('app/public/payments/' . $month_year),$final_name);

        return 'payments/'. $month_year . '/' . $final_name;
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::find($id);
        if (!$payment)
        {
            return response()->json(['error' => 'Payment not found'], 404);
        }
        $validatedData = $request->validate([
            'user_id' => 'required',
            'transaction_code' => 'required',
            'image' => 'required'
        ]);

        $payment->user_id = $validatedData['user_id'];
        $payment->transaction_code = $validatedData['transaction_code'];

        if ($request->hasFile('image'))
        {
            $fileAdd = $request->file('image');
            if($payment->image)
            {
                unlink(storage_path('app/public/' . $payment->image));
            }
            $newFile = $this->storeFile($fileAdd);
            $payment->image = $newFile;
        }
        $payment->save();

        return response()->json([
            'message' => 'Payment updated',
            'Payment' => $payment]);
    }

    public function delete($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
        if($payment->image)
        {
            unlink(storage_path('app/public/' . $payment->image));
        }
        $payment->delete();

        return response()->json(['message' => 'Payment deleted']);
    }
}
