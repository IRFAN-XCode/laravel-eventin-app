<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\EventController;
use App\Http\Middleware\RoleMiddleware;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/events', [EventController::class, 'index']);
Route::get('/event-detail/{id}', [EventController::class, 'show']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register-organizer', [AuthController::class, 'registerOrganizer']);
Route::post('/login-organizer', [AuthController::class, 'loginOrganizer']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware([RoleMiddleware::class. ':user'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::get('/my-tickets', [TransactionController::class, 'getMyTickets']);

    Route::put('/profile/edit-profile', [ProfileController::class, 'updateProfile']);
    Route::delete('/profile/delete', [ProfileController::class, 'deleteAccount']);

    Route::put('/change-password', [AuthController::class, 'changePassword']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/refresh', [AuthController::class, 'refreshToken']);

    Route::get('/detail-ticket/{kode_transaksi}', [TransactionController::class, 'showDetailTicket']);

    Route::post('/checkout-event', [TransactionController::class, 'checkout']);

});

Route::middleware([RoleMiddleware::class. ':organizer'])->group(function () {
    
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/transactions/{id}/konfirmasi', [TransactionController::class, 'konfirmasiStatusAdmin']);

});

Route::get('/transactions/{kode_transaksii}/download_pdf', [TransactionController::class, 'downloadTiketPDF']);
