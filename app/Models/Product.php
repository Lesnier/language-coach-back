<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Product extends Model
{
    protected $guarded = [];
    public function bills()
    {
        return $this->belongsToMany(Bill::class, 'bills_products');
    }
}
