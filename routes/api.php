<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CheckController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Schema;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::post('login', [UserController::class,'login']);
Route::post('register',[UserController::class,'register']);

Route::post('/forgotPassword', [UserController::class, 'forgotPassword']);
Route::post('/resetPassword', [UserController::class, 'resetPassword']);


Route::middleware('auth:api')->group(function () {
    Route::post('/updateUser', [UserController::class, 'updateUser']);
    Route::get('/getUser',[UserController::class,'getUser']);
    Route::post('/changePassword', [UserController::class,'changePassword']);
    Route::delete('/deleteAccount', [UserController::class, 'deleteAccount']);
    Route::post('/logout', [UserController::class, 'logout']);

    Route::post('/createtask', [TaskController::class, 'createTask']);
    Route::post('/updateTask/{taskid}', [TaskController::class, 'updateTask']);
    Route::get('/getTaskById/{taskid}', [TaskController::class, 'getTaskById']);
    Route::get('/getAllTask', [TaskController::class, 'getAllTask']);
    Route::delete('/deleteTask/{taskid}', [TaskController::class, 'deleteTask']);
});

Route::any('unauthenticated', function(){
    return response()->json(
     ['status' => false,'message' => 'Unauthenticated',], 401);
 })->name('login');  // sactum middleware redirect to login named route so we named this route login
 ///////////////////////////////// will work if invalid route called   /////////////////////////////////////////////
 Route::any('{any}', function(){
     return response()->json([
         'status'    => false,
         'message'   => 'Route Not Found.',
     ], 404);
 })->where('any', '.*');


//  Route::post('/checkemails', [CheckController::class, 'verifyEmail']);
