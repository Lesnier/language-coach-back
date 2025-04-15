<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Availability;
use App\Http\Requests\StoreAgendaRequest;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    // ...existing code...

    /**
     * Store a newly created agenda in storage.
     *
     * @param  \App\Http\Requests\StoreAgendaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAgendaRequest $request)
    {
        // At this point, the professor has already been validated as available
        // via the custom form request

        // Get the request data
        $date = $request->date;
        $requestTime = $request->time;

        // Query to check availability using the correct column structure
        $isAvailable = Availability::where('user_id', $request->professor_id)
            ->where('date', $date)
            ->where('start_time', '<=', $requestTime)
            ->where('end_time', '>', $requestTime)
            ->where('is_available', 1)
            ->exists();

        // Only create the agenda if the professor is available
        if (!$isAvailable) {
            return response()->json([
                'message' => 'Professor is not available at the requested time'
            ], 422);
        }

        $agenda = new Agenda();
        $agenda->professor_id = $request->professor_id;
        $agenda->user_id = auth()->id(); // Set the authenticated student as the user
        $agenda->date = $date; // Store as date
        $agenda->time = $requestTime;
        $agenda->state = 'Active'; // Default state
        $agenda->save();
        
        // Load the professor relationship for the response
        $agenda->load('professor');
        
        return response()->json([
            'message' => 'Agenda created successfully',
            'data' => $agenda
        ], 201);
    }

    public function index()
    {
        $agendas = Agenda::with(['professor', 'student'])->get();
        
        return response()->json([
            'message' => 'Agendas retrieved successfully',
            'agendas' => $agendas
        ]);
    }

    // ...existing code...
}