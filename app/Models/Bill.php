<?php

namespace App\Models;

use Carbon\Carbon;
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

    public static function isStartMont():bool
    {
        $day = Carbon::now()->day;
        if($day>=1 && $day<=5)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function getMonthSubscriptions()
    {
        $month = Carbon::now()->month;

         return  Subscription::with('products')
            ->where(function($query) use ($month) {
                $query->whereMonth('period_start', '<=', $month)
                    ->whereMonth('period_end', '>=', $month);
            })
            ->orWhere(function($query) use ($month) {
                $query->whereMonth('period_start', '<', $month)
                    ->whereMonth('period_end', '>', $month);
            })
             ->orWhere(function($query) use ($month) {
                 $query->whereMonth('period_start', '>', $month)
                     ->whereMonth('period_end', '>', $month);
             })
             ->orWhere(function($query) use ($month) {
                 $query->whereMonth('period_start', '>', $month)
                     ->whereMonth('period_end', '<', $month);
             })
            ->where('status', 'Active')
            ->get();
    }

    public static function billExist($subscription_id): bool
    {
        $subscription = Subscription::find($subscription_id);
        $bill = Bill::with('subscription')
            ->whereMonth('emission_date','=',Carbon::now()->month)
            ->whereYear('emission_date','=',Carbon::now()->year)
            ->where('subscription_id','=',$subscription->id)
            ->where('user_id','=',$subscription->user_id)
            ->get();

        if($bill->count()>0){
            return true;
        }
        else{
            return false;
        }
    }

    public static function getTotalProducts($subscription_id)
    {
        $subscription_products = Subscription::with('products')
            ->find($subscription_id);
        $total = 0;
        if($subscription_products->count()>0){
            $products = $subscription_products->products;
            foreach ($products as $p){
                $total = $total + ($p->total);
            }
        }
        return $total;
    }

    public static function generateBill()
    {
        $monthly_subs = Bill::getMonthSubscriptions();
        $generated_bills = [];
        foreach($monthly_subs as $s)
        {
            if(!Bill::billExist($s->id)){
                $subtotal = Bill::getTotalProducts($s->id);
                $tax = setting('site.default_tax');
                $amount = $subtotal * $tax / 100;
                $new_bill = Bill::create([
                    'description' => 'Bill description',
                    'emitter' => setting('site.default_institution'),
                    'user_id' => $s->user_id,
                    'subscription_id' => $s->id,
                    'notified' => false,
                    'emission_date' => Carbon::now()->toDate(),
                    'status' => 'Not paid',
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'amount_tax' => $amount,
                    'total' => $subtotal + $amount,
                ]);
                $new_bill->products()->attach($s->products);
                $generated_bills[] = $s->id;
            }
        }
        if(count($generated_bills)>0)
        {
            return count($generated_bills) . " bills generated.";
        }
        else
        {
            return "0 bills generated.";
        }
    }
}
