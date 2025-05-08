<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validatedData = $request->validate([
            'image' => 'required|string',
            'billId' => 'required'
        ]);

        $payment = new Payment();
        $payment->user_id = auth()->id();
        $payment->transaction_code = 't555';

        if ($request->input('image')) {
            $base64Image = $request->input('image');
            $extension = 'png';

            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                $extension = strtolower($matches[1]);
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
            }

            $imageData = base64_decode($base64Image);
            if ($imageData === false) {
                return response()->json(['error' => 'Invalid base64 image'], 422);
            }

            $filename = uniqid() . '.' . $extension;
            $month_year = Carbon::now()->monthName . Carbon::now()->year;
            $filePath = 'payments/' . $month_year . '/' . $filename;

            Storage::disk('public')->put($filePath, $imageData);
            $payment->image = $filePath;
        }

        $payment->save();

        $bill = Bill::find($request->input('billId'));
        $bill->payment_id = $payment->id;
        $bill->save();

        return response()->json([
            'message' => 'Payment created',
            'payment' => $payment
        ], 201);
    }

    private function storeFile($file)
    {
        // Deprecated as base64 image is handled in the store method
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $validatedData = $request->validate([
            'image' => 'required|string'
        ]);

        if ($request->input('image')) {
            Log::info("Base64 image received successfully");

            $base64Image = $request->input('image');
            $extension = 'png'; // valor por defecto en caso de no encontrar MIME

            // Extraer encabezado y base64
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                $extension = strtolower($matches[1]); // Ej: png, jpeg, jpg
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
            }

            $imageData = base64_decode($base64Image);
            if ($imageData === false) {
                return response()->json(['error' => 'Invalid base64 image'], 422);
            }

            // Eliminar imagen anterior si existe
            if ($payment->image) {
                $oldPath = storage_path('app/public/' . $payment->image);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // Generar nombre de archivo con extensión original
            $filename = uniqid() . '.' . $extension;
            $month_year = Carbon::now()->monthName . Carbon::now()->year;
            $filePath = 'payments/'. $month_year . '/' . $filename;

            // Guardar imagen en storage/app/public/payments
            Storage::disk('public')->put($filePath, $imageData);

            $payment->image = $filePath;
        }

        $payment->save();

        return response()->json([
            'message' => 'Payment updated',
            'payment' => $payment
        ]);
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
