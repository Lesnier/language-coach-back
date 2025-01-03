<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with('user')->get();
        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required',
            'transaction_code' => 'required',
            'image' => 'required'
        ]);

        $payment = new Payment();
        $payment->user_id = $validatedData['user_id'];
        $payment->transaction_code = $validatedData['transaction_code'];

        if ($request->hasFile('image'))
        {
            //$originalFilePath = $request->file('file')->getPathname();
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
        $final_name = date("His") . "_" . $name_file . "." . $extension;
        $file->move(public_path('/storage/payments'),$final_name);

        return public_path('/storage/payments') . "/". $final_name;
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
            //$originalFilePath = $request->file('file')->getPathname();
            $fileAdd = $request->file('image');
            //Storage::delete($payment->image);
            unlink($payment->image);
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

        //Storage::delete($payment->image);
        unlink($payment->image);
        $payment->delete();

        return response()->json(['message' => 'Payment deleted']);
    }
}
