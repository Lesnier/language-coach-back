<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AgendaReminder;
use App\Models\Agenda;
use App\Models\Availability;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        $availability = Availability::where('day_of_week', $validatedData['date'])
            ->where(function($query) use ($validatedData)
            {
                $query->where('start_time', '<=', Carbon::parse($validatedData['time'])->toTimeString())
                        ->where('end_time', '>', Carbon::parse($validatedData['time'])->toTimeString());
            })
            ->where('is_available','=',1)
            ->with('user')
            ->get();

        $professor_id = $availability->first()->user_id;

        $agenda = Agenda::create([
            'date' => $validatedData['date'],
            'time' => $validatedData['time'],
            'user_id' => $user->id,
            'professor_id' => $professor_id,
            'state' => 'Active'
        ]);

        return response()->json([
            'message' => 'Agenda make success',
            'agenda' => $agenda,
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

    if($this->isWeekend($validatedData['date']))
    {
        return response()->json([
            'error'=>'The selected date is a weekend.'
        ]);
    }else
    {
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
    function isProfessor($user_id):bool
    {
        $user = User::find($user_id);
        $rol = $user->role->id;
        return $rol == 3;
    }
    public function haveAgenda($date,$user_id):bool
    {
        return Agenda::where('user_id', $user_id)
            ->whereDate('date', $date)
            ->exists();

    }
    public function cancelAgenda($agenda_id)
    {
        $agenda = Agenda::find($agenda_id);
        if (!$agenda)
        {
            return response()->json([
                'message' => 'Agenda not found',
            ],404);
        }
        else
        {
            $agenda->state = "Cancelled";
            $agenda->save();
            return response()->json([
                'message' => 'Agenda cancelled',
                'agenda' => $agenda
            ],201);
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

        $existEmail = User::where('email','=',$email)->first();
        if($existEmail)
        {
            Mail::to($email)->send(new AgendaReminder($emailBody));
            return response()->json(['message' => 'Email sent to ' . $email]);
        }
        else
        {
            return response()->json(['error' => $email . ' email not found. ']);
        }
//        $agendasHoy = Agenda::whereDate('date', Carbon::now()->toDateString())->get();
//        foreach ($agendasHoy as $agenda)
//        {
//        }
        //return response()->json('correo enviado');
//        return response()->json(Carbon::now()->toTimeString());
    }
}
