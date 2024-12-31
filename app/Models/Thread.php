<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Thread extends Model
{
    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }
    public function threadreplys()
    {
        return $this->hasMany(Threadreply::class);
    }
}
