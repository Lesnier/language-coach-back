<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Availability;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfessorController extends Controller
{
    /**
     * Get all professors with their availabilities
     *
     * @return \Illuminate\Http\Response
     */
    public function getAvailableProfessors(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'date' => 'sometimes|date|date_format:Y-m-d',
        ]);

        // Get users with role_id = 3 (professors)
        $query = User::where('role_id', 3);
        
        // Get professors with their availabilities
        $professors = $query->with(['availabilities' => function($query) use ($request) {
            // If date is provided, filter availabilities by date
            if ($request->has('date')) {
                $query->whereDate('date', $request->date);
            }
        }])->get();
        
        return response()->json([
            'message' => 'Professors retrieved successfully',
            'professors' => $professors
        ]);
    }
}