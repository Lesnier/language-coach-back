<?php

namespace App\Providers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('not_weekend', function($attribute, $value, $parameters, $validator) {
            return $this->isWeekend($value) === false;
        });
        Validator::extend('is_professor', function($attribute, $value, $parameters, $validator) {
            return $this->isProfessor($value) === true;
        });
    }

    function isWeekend($date) {
        $weekday = date('N', strtotime($date));
        return ($weekday >= 6); // 6 y 7 representan sábados y domingos
    }

    function isProfessor($user_id)
    {
        $user = User::find($user_id);
        $rol = $user->role->id;
        return $rol == 3;
    }

}
