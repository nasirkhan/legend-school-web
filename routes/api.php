<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest')
    ->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'show'])->name('api.user');

    Route::prefix('users')
        ->name('api.users.')
        ->controller(UserController::class)
        ->group(function () {
            Route::get('/profile/{username?}', 'show')->name('profile');
            Route::match(['put', 'patch'], '/profile', 'update')->name('updateProfile');
        });
});
