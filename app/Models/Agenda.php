<?php

namespace App\Models;

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
