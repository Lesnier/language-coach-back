<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Availability;

class ProfessorValidator
{
    /**
     * Check if a user is a professor with availability on a specific date
     *
     * @param int $professorId The ID of the professor to check
     * @param string $date The date to check availability for (Y-m-d format)
     * @return bool
     */
    public static function isProfessorAvailableOnDate($professorId, $date)
    {
        // First check if user exists and is a professor (role_id = 3)
        $professor = User::where('id', $professorId)
            ->where('role_id', 3)
            ->first();
            
        if (!$professor) {
            return false;
        }
        
        // Then check if professor has availability on that date
        $hasAvailability = Availability::where('user_id', $professorId)
            ->whereDate('date', $date)
            ->exists();
            
        return $hasAvailability;
    }
}