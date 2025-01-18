<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Bill extends Model
{
    protected $guarded = [];
    public function products()
    {
        return $this->belongsToMany(Product::class,'bills_products');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
