<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Payment extends Model
{
    protected $fillable = ['user_id','image','transaction_code'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
