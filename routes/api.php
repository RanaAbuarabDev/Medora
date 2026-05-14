<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\AdminSettingsController;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\MasterTestController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LabManagerController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\LabRatingController;
// use app\Http\Controllers\LabScheduleController;
use App\Http\Controllers\LabScheduleController;
use App\Http\Controllers\LabTestController;
use App\Http\Controllers\AdminDashboardController;
//use App\Http\Controllers\Admin\SettingsController;

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
        
       
        Route::get('/appointments', [AppointmentController::class, 'index']);
        
        Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
        
        Route::middleware('role:patient')->group(function () {
            Route::post('/appointments', [AppointmentController::class, 'store']);
        });
        
        
        Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);
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

    Route::middleware(['auth:sanctum', 'role:lab_manager'])->group(function () {
        Route::get('lab/schedule', [LabScheduleController::class, 'index']);
        Route::put('lab/schedule/{day_of_week}', [LabScheduleController::class, 'update']);
    });


    Route::middleware('auth:sanctum')->group(function () {
    
        
        Route::get('/lab-ratings', [LabRatingController::class, 'index']); 
        
       
        Route::post('/lab-ratings', [LabRatingController::class, 'store']);
        
        
        Route::get('/laboratories/{id}/ratings', [LabRatingController::class, 'getLabRatings']);
    });

    Route::middleware('auth:sanctum')->group(function () {
    
        Route::get('/tests/{testId}/search', [MasterTestController::class, 'searchTest']);
        Route::get('/laboratories/{labId}/slots', [LaboratoryController::class, 'getSlots']);

    });



    


    // مجموعة روابط مدير المنصة (Platform Admin)
    Route::prefix('admin')->group(function () {

        // 1. الإحصائيات والرسوم البيانية (الكروت العلوية)
        Route::get('/statistics', [AdminDashboardController::class, 'getStatistics']);

        // 2. قائمة المخابر (الجدول اللي تحت)
        Route::get('/labs', [AdminDashboardController::class, 'getLabsList']);

        // 3. إدارة المخابر (الإجراءات - Actions)
        Route::prefix('labs')->group(function () {
            
            // إضافة مخبر جديد (الزر الأزرق)
            Route::post('/', [LaboratoryController::class, 'store']);
            
            // عرض تفاصيل مخبر محدد (أيقونة العين)
            Route::get('/{id}', [LaboratoryController::class, 'show']);
            
            // قبول مخبر (زر قبول)
            Route::put('/{id}/approve', [LaboratoryController::class, 'approve']);
            
            // رفض مخبر (زر رفض)
            Route::put('/{id}/reject', [LaboratoryController::class, 'reject']);
            
            // حظر مخبر (أيقونة المنع الحمراء)
            Route::put('/{id}/block', [LaboratoryController::class, 'block']);
            
            // جلب الفترات الزمنية المتاحة لمخبر (للمريض أو الإدارة)
            Route::get('/{id}/slots', [LaboratoryController::class, 'getSlots']);
        });

    });


    Route::group(['prefix' => 'admin', 'middleware' => ['auth:sanctum', 'role:admin']], function () {
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/users/{id}/reset-password', [AdminUserController::class, 'resetPassword']);
        Route::put('/users/{id}/status', [AdminUserController::class, 'updateStatus']);
    });


    Route::group(['prefix' => 'admin', 'middleware' => ['auth:sanctum', 'role:admin']], function () {
    
      
        Route::get('/payments', [PaymentController::class, 'index']);

        
        Route::get('/analytics', [AnalyticsController::class, 'index']);

        
        Route::get('settings/', [AdminSettingsController::class, 'index']);
        Route::post('settings/update', [AdminSettingsController::class, 'update']);
        
        Route::post('/payments/send-reminders', [PaymentController::class, 'sendReminders']);
        Route::post('/payments/{id}/confirm', [PaymentController::class, 'markAsPaid']);
        
    });
});

//16|yPiMI0PfGquuLC0wijLMx5PGqp2BktCjqAkOshIF66850244