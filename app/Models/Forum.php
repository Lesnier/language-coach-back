<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Forum extends Model
{
    public function threads()
    {
        return $this->hasMany(Thread::class);
    }
}
