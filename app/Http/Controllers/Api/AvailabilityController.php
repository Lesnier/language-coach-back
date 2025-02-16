<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function getAvailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
        ]);

        $date = $request->input('date');
        $time = $request->input('time') . ':00';

        $availabilities = Availability::where('day_of_week', $date)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>', $time)
            ->where('is_available', true)
            ->get();

        return response()->json([
            'data' => $availabilities,
        ]);
    }
}
