<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AgendaReminder;
use App\Models\Agenda;
use App\Models\Availability;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
            ->with('professor')
            ->get();

        return response()->json([
            'message' => 'Agendas retrieved successfully',
            'agendas' => $agendas,
        ]);
    }

    /**
     * Store a newly created agenda in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'professor_id' => 'required|exists:users,id',
            'date' => 'required|date|date_format:Y-m-d',
            'time' => 'required|date_format:H:i:s',
        ]);

        // Check if professor has availability on the requested date and time
        $hasAvailability = \App\Models\Availability::where('user_id', $request->professor_id)
            ->whereDate('date', $request->date)
            ->whereTime('start_time', '<=', $request->time)
            ->whereTime('end_time', '>', $request->time)
            ->exists();
            
        if (!$hasAvailability) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => [
                    'professor_id' => ['The selected professor is not available at the requested date and time.']
                ]
            ], 422);
        }
        
        // Create the agenda
        $agenda = new \App\Models\Agenda();
        $agenda->professor_id = $request->professor_id;
        $agenda->user_id = auth()->id(); // Set the authenticated student as the user
        $agenda->date = $request->date;
        $agenda->time = $request->time;
        $agenda->state = 'Active'; // Default state
        $agenda->save();
        
        // Load the professor relationship for the response
        $agenda->load('professor');
        
        return response()->json([
            'message' => 'Agenda created successfully',
            'data' => $agenda
        ], 201);
    }

    public function update(Request $request, $agenda_id)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'professor_id' => 'required|exists:users,id',
        ]);

        $agenda = Agenda::find($agenda_id);

        $agenda->date = $validatedData['date'];
        $agenda->time = $validatedData['time'];
        $agenda->professor_id = $validatedData['professor_id'];

        if ($this->isWeekend($validatedData['date'])) {
            return response()->json([
                'error' => 'The selected date is a weekend.'
            ]);
        } else {
            $agenda->save();

            return response()->json([
                'message' => 'Agenda updated success',
                'agenda' => $agenda,
            ], 201);
        }
    }


    function isWeekend($date): bool
    {
        $weekday = date('N', strtotime($date));
        return ($weekday >= 6); // 6 y 7 representan sábados y domingos
    }

    function isProfessor($user_id): bool
    {
        $user = User::find($user_id);
        $rol = $user->role->id;
        return $rol == 3;
    }

    public function haveAgenda($date, $user_id): bool
    {
        return Agenda::where('user_id', $user_id)
            ->whereDate('date', $date)
            ->exists();
    }

    public function cancelAgenda($agenda_id)
    {
        $agenda = Agenda::find($agenda_id);
        if (!$agenda) {
            return response()->json([
                'message' => 'Agenda not found',
            ], 404);
        } else {
            $agenda->state = "Cancelled";
            $agenda->save();
            return response()->json([
                'message' => 'Agenda cancelled',
                'agenda' => $agenda
            ], 201);
        }
    }

    public function agendaConfirmationEmail(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'emailBody' => 'required',
        ]);

        $email = $validatedData['email'];
        $emailBody = $validatedData['emailBody'];

        $existEmail = User::where('email', '=', $email)->first();
        if ($existEmail) {
            Mail::to($email)->send(new AgendaReminder($emailBody));
            return response()->json(['message' => 'Email sent to ' . $email]);
        } else {
            return response()->json(['error' => $email . ' email not found. ']);
        }
//        $agendasHoy = Agenda::whereDate('date', Carbon::now()->toDateString())->get();
//        foreach ($agendasHoy as $agenda)
//        {
//        }
        //return response()->json('correo enviado');
//        return response()->json(Carbon::now()->toTimeString());
    }

    public function destroy($agenda_id)
    {
        $agenda = Agenda::find($agenda_id);
        
        if (!$agenda) {
            return response()->json([
                'message' => 'Agenda not found',
            ], 404);
        }
        
        $agenda->delete();
        
        return response()->json([
            'message' => 'Agenda deleted successfully',
        ], 200);
    }
}
