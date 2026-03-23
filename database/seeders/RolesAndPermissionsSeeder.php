<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. تنظيف الكاش (خطوة أساسية دائماً)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. تعريف الصلاحيات (Permissions) مقسمة حسب الوظيفة
        
        // صلاحيات مدير المنصة (Platform Admin)
        $platformPermissions = [
            'manage_labs', 'approve_labs', 'manage_subscriptions', 'view_platform_stats',
            'manage_all_users', 'monitor_payments', 'set_commissions', 'manage_ai_settings'
        ];

        // صلاحيات مدير المخبر (Lab Manager)
        $managerPermissions = [
            'view_lab_dashboard', 'manage_staff', 'manage_patients', 'manage_tests_types',
            'manage_prices', 'view_all_appointments', 'view_all_invoices', 'generate_financial_reports',
            'backup_database', 'edit_lab_profile', 'monitor_staff_performance'
        ];

        // صلاحيات المساعد المخبري (Lab Assistant)
        $assistantPermissions = [
            'view_today_appointments', 'view_patient_data', 'change_appointment_status',
            'upload_test_results', 'edit_test_results', 'send_patient_notifications'
        ];

        // صلاحيات موظف الاستقبال (Receptionist)
        $receptionistPermissions = [
            'manage_appointments', 'register_patients', 'view_patient_profile',
            'send_appointment_reminders', 'manage_patient_queries'
        ];

        // صلاحيات المريض (Patient)
        $patientPermissions = [
            'view_my_results', 'book_appointment', 'view_my_invoices'
        ];

        // إنشاء كل الصلاحيات في قاعدة البيانات
        $allPermissions = array_unique(array_merge(
            $platformPermissions, $managerPermissions, 
            $assistantPermissions, $receptionistPermissions, $patientPermissions
        ));

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission,'guard_name' => 'api']);
            
        }

        // 3. إنشاء الأدوار وربطها بالصلاحيات

        // مدير المنصة (Super Admin) - نعطيه كل شيء حرفياً
        $superAdmin = Role::firstOrCreate(['name' => 'admin','guard_name' => 'api']);
        $superAdmin->givePermissionTo(Permission::all());

        // مدير المخبر
        $labManager = Role::firstOrCreate(['name' => 'lab_manager','guard_name' => 'api']);
        $labManager->givePermissionTo($managerPermissions);
        $labManager->givePermissionTo(['upload_test_results', 'view_patient_data']); // ميزات إضافية للمدير

        // المساعد المخبري
        $labAssistant = Role::firstOrCreate(['name' => 'lab_assistant','guard_name' => 'api']);
        $labAssistant->givePermissionTo($assistantPermissions);

        // موظف الاستقبال
        $receptionist = Role::firstOrCreate(['name' => 'receptionist','guard_name' => 'api']);
        $receptionist->givePermissionTo($receptionistPermissions);

        // المريض
        $patient = Role::firstOrCreate(['name' => 'patient','guard_name' => 'api']);
        $patient->givePermissionTo($patientPermissions);
    }
}