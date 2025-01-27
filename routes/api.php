<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Schema;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::post('login', [UserController::class,'login']);
Route::post('register',[UserController::class,'register'])->name('register_user');


Route::middleware('auth:api')->group(function () {
    Route::post('/createtask', [TaskController::class, 'createTask']);
    Route::post('/updateTask/{taskid}', [TaskController::class, 'updateTask']);
    Route::get('/getTaskById/{taskid}', [TaskController::class, 'getTaskById']);
    Route::get('/getAllTask', [TaskController::class, 'getAllTask']);
    Route::get('/deleteTask/{taskid}', [TaskController::class, 'deleteTask']);
});



