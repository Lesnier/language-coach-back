<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function getAvailability()
    {
        $availabilities = Availability::where('is_available', true)->select(['day_of_week', 'start_time', 'end_time'])->get();
        return response()->json(
            $availabilities
        );
    }
}
