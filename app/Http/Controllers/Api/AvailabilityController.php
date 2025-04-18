<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function getAvailability(Request $request)
    {
        $availabilities = Availability::where('is_available', true)
            ->select(['id', 'user_id', 'day_of_week', 'start_time', 'end_time'])
            ->get();

        // Transform the availabilities to rename user_id to professor_id and drop the user object
        $formattedAvailabilities = $availabilities->map(function ($availability) {
            return [
                'id' => $availability->id,
                'professor_id' => $availability->user_id, // Renamed user_id to professor_id
                'date' => $availability->day_of_week,
                'start_time' => $availability->start_time,
                'end_time' => $availability->end_time
            ];
        });

        return response()->json([
            'message' => 'Availabilities retrieved successfully',
            'availabilities' => $formattedAvailabilities,
        ]);
    }
}
