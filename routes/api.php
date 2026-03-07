<?php

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LabManagerController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Auth)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register/patient', [AuthController::class, 'registerPatient']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Auth Required)
|--------------------------------------------------------------------------
*/

Route::post(
    '/verify-registration-otp',
    [AuthController::class, 'verifyRegistrationOtp']
);


Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
});

Route::get('/auth/google/callback', function () {
    $user = Socialite::driver('google')->user();
    
    
    dd($user); 
});
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        Route::post('/lab-managers', [AdminController::class, 'createLabManager']);
    });

    /*
    |--------------------------------------------------------------------------
    | Lab Manager Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:lab_manager','auth:sanctum'])->group(function () {
        Route::post('/lab-assistants', [LabManagerController::class, 'createAssistant']);
        Route::post('/receptionists', [LabManagerController::class, 'createReceptionist']);
    });

    /*
    |--------------------------------------------------------------------------
    | Shared Routes (All Auth Users)
    |--------------------------------------------------------------------------
    */
    // Route::get('/me', function () {
    //     return auth()->user();
    // });
});
