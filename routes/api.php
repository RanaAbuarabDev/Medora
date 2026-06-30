<?php

use App\Http\Controllers\Admin\AdminController as AdminAdminController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\AdminSettingsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\MasterTestController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LabManagerController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\LabRatingController;
use App\Http\Controllers\LabTestController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Patient\PatientProfileController;
use App\Http\Controllers\Patient\PatientNotificationController;
use App\Http\Controllers\Reception\ReceptionistDashboardController;
use App\Http\Controllers\LabAssistant\LabAssistantDashboardController;
use App\Http\Controllers\LabAssistant\LabInventoryUsageController;
use App\Http\Controllers\LabAssistant\LabResultController as LabAssistantLabResultController;
use App\Http\Controllers\LabManager\DashboardController;
use App\Http\Controllers\LabManager\InvoiceController;
use App\Http\Controllers\LabManager\LabInventoryController;
use App\Http\Controllers\LabManager\LabPatientController;
use App\Http\Controllers\LabManager\LabPatientProfileController;
use App\Http\Controllers\LabManager\LabResultController;
use App\Http\Controllers\LabManager\LabScheduleController;
use App\Http\Controllers\LabManager\LabStaffController;
use App\Http\Controllers\LabManager\OfferController;
use App\Http\Controllers\LabManager\OperationController;
use App\Http\Controllers\LabManager\ReportController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Auth Required)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register/patient', [AuthController::class, 'registerPatient']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp', [AuthController::class, 'verifyResetOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{id}', [CategoryController::class, 'show']);



Route::post('/verify-registration-otp', [AuthController::class, 'verifyRegistrationOtp']);
Route::post('/logout', [AuthController::class, 'logout']);
/*
|--------------------------------------------------------------------------
| Protected Routes (Auth Required Via Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth Actions
    Route::get('/tests/search', [MasterTestController::class, 'searchMasterTest']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Global Appointments Actions
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);
    Route::get('patient/labs/{labId}/active-offers', [OfferController::class, 'indexForPatient']);
    // Patient Specific Actions
    Route::middleware('role:patient')->group(function () {
        Route::post('/appointments', [AppointmentController::class, 'store']);
    });

    Route::get('patient/notifications', [PatientNotificationController::class, 'index']);
    
    // تحديث الإشعار ليصبح مقروءاً عند النقر
    Route::post('patient/notifications/{id}/read', [PatientNotificationController::class, 'markAsRead']);
    // جلب وعرض بيانات الحساب والملاحظات الطبية للمريض
    Route::get('patient/profile', [PatientProfileController::class, 'show']);
    Route::post('patient/profile/update', [PatientProfileController::class, 'update']);


    // Medical Ratings Actions
    Route::get('/lab-ratings', [LabRatingController::class, 'index']); 
    Route::post('/lab-ratings', [LabRatingController::class, 'store']);
    Route::get('/laboratories/{id}/ratings', [LabRatingController::class, 'getLabRatings']);

    // Laboratories Core Search & Slots
    // البحث المتعدد عن باقة تحاليل معاً
    Route::get('/tests/search-multiple', [MasterTestController::class, 'searchMultipleTests']);
    Route::get('/tests/{testId}/search', [MasterTestController::class, 'searchTest']);
    Route::get('/laboratories/{labId}/slots', [LaboratoryController::class, 'getSlots']);
    Route::get('get-test-category/{id}', [MasterTestController::class, 'getByCategory']);




    Route::apiResource('admin/master-tests', MasterTestController::class);
    /*
    |--------------------------------------------------------------------------
    | Platform Admin Specific Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        Route::post('/lab-managers', [AdminAdminController::class, 'createLabManager']);
        Route::post('/admin/register-lab', [AdminAdminController::class, 'registerLabWithManager']);
       // Route::apiResource('admin/master-tests', MasterTestController::class);
        
        Route::prefix('admin')->group(function () {
            Route::get('/statistics', [AdminDashboardController::class, 'getStatistics']);
            Route::get('/labs', [AdminDashboardController::class, 'getLabsList']);
            Route::get('/users', [AdminUserController::class, 'index']);
            Route::post('/users/{id}/reset-password', [AdminUserController::class, 'resetPassword']);
            Route::put('/users/{id}/status', [AdminUserController::class, 'updateStatus']);
            Route::get('/payments', [PaymentController::class, 'index']);
            Route::get('/analytics', [AnalyticsController::class, 'index']);
            Route::get('settings/', [AdminSettingsController::class, 'index']);
            Route::post('settings/update', [AdminSettingsController::class, 'update']);
            Route::post('/payments/send-reminders', [PaymentController::class, 'sendReminders']);
            Route::post('/payments/{id}/confirm', [PaymentController::class, 'markAsPaid']);
            Route::delete('/laboratories/{id}', [AdminAdminController::class, 'destroyLab']);

            Route::prefix('labs')->group(function () {
                Route::post('/', [LaboratoryController::class, 'store']);
                // تمت إزالة السطور المكررة لـ approved / reject لعدم تشتيت النظام
                Route::get('/{id}', [LaboratoryController::class, 'show']);
                Route::put('/{id}/approve', [LaboratoryController::class, 'approve']);
                Route::put('/{id}/reject', [LaboratoryController::class, 'reject']);
                Route::put('/{id}/block', [LaboratoryController::class, 'block']);
                Route::get('/{id}/slots', [LaboratoryController::class, 'getSlots']);
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Lab Manager Specific Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:lab_manager')->group(function () {
        Route::post('/lab-assistants', [LabManagerController::class, 'createAssistant']);
        Route::post('/receptionists', [LabManagerController::class, 'createReceptionist']);
        Route::get('lab/my-tests', [LabTestController::class, 'index']);
        Route::post('lab/add-test', [LabTestController::class, 'store']);
        Route::get('lab/schedule', [LabScheduleController::class, 'index']);
        Route::put('lab/schedule/{day_of_week}', [LabScheduleController::class, 'update']);
        Route::get('/dashboard/stats', [DashboardController::class,'index']);
        Route::get('/operations', [OperationController::class, 'index']);
        Route::get('/lab/available-master-tests', [LabTestController::class, 'getAvailableMasterTests']);
        Route::get('/lab/results-dashboard', [LabResultController::class, 'index']);
        Route::get('/lab/patients', [LabPatientController::class, 'index']);
        Route::get('/lab/patients/{id}', [LabPatientProfileController::class, 'show']);
        Route::get('/lab/staff', [LabStaffController::class, 'index']);
        Route::get('invoices', [InvoiceController::class, 'index']);
        Route::get('reports', [ReportController::class, 'index']);
        Route::apiResource('inventory', LabInventoryController::class);
        Route::get('offers/', [OfferController::class, 'indexForAdmin']);
        Route::post('offers/', [OfferController::class, 'store']);
        Route::put('offers/{id}', [OfferController::class, 'update']);
        Route::delete('offers/{id}', [OfferController::class, 'destroy']);


        Route::prefix('lab/staff')->group(function () {
            Route::get('/', [LabStaffController::class, 'index']);               
            Route::get('/{id}', [LabStaffController::class, 'show']);            
            Route::put('/{id}', [LabStaffController::class, 'update']);          
            Route::patch('/{id}/toggle-block', [LabStaffController::class, 'toggleBlock']); 
            Route::post('/', [LabStaffController::class, 'store']); 
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Receptionist Specific Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('reception')->group(function () {
        Route::get('/dashboard', [ReceptionistDashboardController::class, 'index']);
        Route::get('/available-slots', [ReceptionistDashboardController::class, 'getSlots']);
        Route::post('/book-appointment', [ReceptionistDashboardController::class, 'bookAppointment']);
        Route::get('/search-patient', [ReceptionistDashboardController::class, 'searchPatient']);
        Route::get('/appointments', [ReceptionistDashboardController::class, 'manageAppointments']);
        Route::get('/patient-profile/{id}', [ReceptionistDashboardController::class, 'getPatientProfile']);
        Route::patch('/invoices/{id}/toggle-payment', [ReceptionistDashboardController::class, 'togglePayment']);
        Route::patch('/appointments/{id}/confirm-attendance', [ReceptionistDashboardController::class, 'confirmAttendance']);
    });

    /*
    |--------------------------------------------------------------------------
    | Lab Assistant Specific Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('lab-assistant')->group(function () {
        Route::get('/dashboard', [LabAssistantDashboardController::class, 'index']);
        
       
        Route::get('/today-appointments', [LabAssistantDashboardController::class, 'todayAppointments']);
        
        Route::post('/appointments/{appointment}/start', [LabAssistantDashboardController::class, 'start']);
        Route::get('/results/search', [LabAssistantLabResultController::class, 'search']);
        Route::post('/appointments/{appointment}/results', [LabAssistantLabResultController::class, 'store']);
        // جلب قائمة تقارير الجدول الرئيسي مع الفلاتر والبحث
        Route::get('/results', [LabAssistantLabResultController::class, 'index']); 
        Route::get('inventory-cards', [LabInventoryUsageController::class, 'getCards']);
        Route::post('appointments/{appointmentId}/consume', [LabInventoryUsageController::class, 'submitUsage']);
    });


    
}); 