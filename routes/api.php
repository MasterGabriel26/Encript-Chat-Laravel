<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

 
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessageController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {return $request->user();});
// Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/messages/send', [MessageController::class, 'sendMessage']);
    Route::get('/messages/{user}', [MessageController::class, 'getMessages']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);