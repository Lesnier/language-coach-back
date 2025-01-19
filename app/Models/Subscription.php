<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Subscription extends Model
{
    public function products()
    {
        return $this->belongsToMany(Product::class,'product_subscription');
    }
}
