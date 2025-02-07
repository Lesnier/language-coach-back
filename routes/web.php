<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;
use TCG\Voyager\Facades\Voyager;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/subscription_price/{id}',[SubscriptionController::class,'getPrice']);
Route::post('/product_prices',[ProductController::class,'getPrices']);

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});
