<?php

use App\Http\Controllers\Api\AgendaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function ()
{
    Route::get('/user-info', [UserController::class, 'showInfo']);
    Route::post('/change-password', [UserController::class, 'changePass']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/agendas', [AgendaController::class, 'index']);
    Route::post('/agendas', [AgendaController::class, 'store']);

    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
});
