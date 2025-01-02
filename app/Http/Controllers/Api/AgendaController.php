<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $currentDate = now()->startOfDay();

        $agendas = Agenda::where('user_id', $user->id)
            ->whereDate('date', '>=', $currentDate)
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        return response()->json([
            'message' => 'Agendas retrieved successfully',
            'agendas' => $agendas,
        ]);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
        ]);

        if($this->isWeekend($validatedData['date']))
        {
            return response()->json([
                'error'=>'The selected is a weekend.'
            ]);
        }

        if($this->haveAgenda($validatedData['date'], auth()->user()->id))
        {
            return response()->json([
                'error'=>'The user have an agenda in the selected date'
            ]);
        }

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

    function isWeekend($date): bool
    {
        $weekday = date('N', strtotime($date));
        return ($weekday >= 6); // 6 y 7 representan sábados y domingos
    }

    public function haveAgenda($date,$user_id):bool
    {
        return Agenda::where('user_id', $user_id)
            ->whereDate('date', $date)
            ->exists();

    }
}
