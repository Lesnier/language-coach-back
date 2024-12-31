<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Threadreply extends Model
{
    protected $fillable = ['thread_id','user_id','response'];
    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }
}
