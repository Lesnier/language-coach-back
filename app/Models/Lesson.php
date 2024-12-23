<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Lesson extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'class_content','module_id','file_id'];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
