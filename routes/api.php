<?php

use App\Http\Controllers\Api\AgendaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\TaskController;
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

    Route::get('/forums', [ForumController::class,'forums']);
    Route::get('/threads', [ForumController::class,'threads']);
    Route::post('/threadreply/{thread_id}', [ForumController::class,'threadreply']);

    Route::get('/tasks',[TaskController::class,'index']);
    Route::post('/tasks',[TaskController::class,'store']);
    Route::post('/tasks/{id}',[TaskController::class,'update']);
    Route::delete('/tasks/{id}',[TaskController::class,'delete']);

    Route::get('/files',[FileController::class,'index']);
    Route::post('/files',[FileController::class,'store']);
    Route::post('/files/{id}',[FileController::class,'update']);
    Route::delete('/files/{id}',[FileController::class,'delete']);
});
