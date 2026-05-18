<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\EventController;
use App\Http\Middleware\RoleMiddleware;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/events', [EventController::class, 'index']);
Route::post('/events', [EventController::class, 'store']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register-organizer', [AuthController::class, 'registerOrganizer']);


Route::middleware([RoleMiddleware::class. ':user'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'getProfile']);

    Route::put('/profile/edit-profile', [ProfileController::class, 'updateProfile']);

    Route::put('/change-password', [AuthController::class, 'changePassword']);

});