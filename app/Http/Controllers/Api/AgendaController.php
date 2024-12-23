<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
        ]);

        $user = auth()->user();

        $agenda = Agenda::create([
            'date' => $validatedData['date'],
            'time' => $validatedData['time'],
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Agenda make success',
            'agenda' => $agenda,
        ], 201);
    }
}
