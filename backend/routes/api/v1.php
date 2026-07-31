<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [
            AuthController::class,
            'register',
        ]);

        Route::post('/login', [
            AuthController::class,
            'login',
        ]);

        Route::get('/email/verify/{id}/{hash}', [
            AuthController::class,
            'verifyEmail',
        ])
            ->middleware('signed')
            ->name('api.v1.auth.verify-email');

        Route::middleware('auth:api')->group(function () {
            Route::get('/me', [
                AuthController::class,
                'me',
            ]);

            Route::post('/logout', [
                AuthController::class,
                'logout',
            ]);
        });
    });
});
