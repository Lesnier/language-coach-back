<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'day_of_week','start_time','end_time', 'is_available'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeProfessor($query)
    {
        if(Auth::user()->role_id == 3)
        {
            return $query->where('professor_id', Auth::user()->id);
        }
        return $query;
    }
}
