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
Route::get('/event-detail/{id}', [EventController::class, 'show']);

// Route::get('/tickets', [TickettController::class, 'index']);
// Route::get('/tickets/{id}', [TicketController::class, 'show']);


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register-organizer', [AuthController::class, 'registerOrganizer']);


Route::middleware([RoleMiddleware::class. ':user'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'getProfile']);

    Route::put('/profile/edit-profile', [ProfileController::class, 'updateProfile']);
    Route::delete('/profile/delete', [ProfileController::class, 'deleteAccount']);

    Route::put('/change-password', [AuthController::class, 'changePassword']);

    Route::post('/logout', [AuthController::class, 'logout']);

});

Route::middleware([RoleMiddleware::class. ':organizer'])->group(function () {
    
    Route::post('/events', [EventController::class, 'store']);

});