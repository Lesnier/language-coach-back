<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Agenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'time',
        'user_id',
        'professor_id',
        'state'
    ];

    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
    }
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
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
