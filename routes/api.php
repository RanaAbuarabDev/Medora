<?php

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MasterTestController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LabManagerController;
use App\Http\Controllers\LabTestController;

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


Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('verify-otp', [AuthController::class, 'verifyResetOtp']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);



Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{id}', [CategoryController::class, 'show']);


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


    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/admin/register-lab', [AdminController::class, 'registerLabWithManager']);
    });
    

    // Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    //     Route::apiResource('admin/categories', CategoryController::class);
    // });

    
    // Route::get('categories', [CategoryController::class, 'index']);
    // Route::get('categories/{id}', [CategoryController::class, 'show']);


    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::apiResource('admin/master-tests', MasterTestController::class);
    });

    Route::middleware(['auth:sanctum'])->group(function(){
        Route::get('get-test-category/{id}',[MasterTestController::class,'getByCategory']);
    });

    

    Route::middleware(['auth:sanctum','role:lab_manager'])->group(function () {
       
        Route::get('lab/my-tests', [LabTestController::class, 'index']);
        Route::post('lab/add-test', [LabTestController::class, 'store']);
    });
});
