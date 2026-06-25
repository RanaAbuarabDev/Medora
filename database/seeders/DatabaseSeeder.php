<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Laboratory;
use App\Models\PatientProfile;
use App\Models\EmployeeProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ أولاً: إنشاء الصلاحيات والأدوار (مهم جداً أن يكون الأول)
        $this->call(RolesAndPermissionsSeeder::class);

        // 2️⃣ ثانياً: إنشاء مدير المنصة (Admin)
        $this->call(AdminSeeder::class);

        // 3️⃣ ثالثاً: إنشاء فئات التحاليل
        $this->call(TestCategorySeeder::class);

        // 4️⃣ رابعاً: إنشاء جداول الدوام والتحاليل الأساسية
        $this->call(LabScheduleSeeder::class);
        $this->call(MasterTestSeeder::class);
        $this->call(MasterItemSeeder::class);

       
       
        $lab = Laboratory::create([
            'name' => 'مختبر ميديورا المركزي',
            'address' => 'دمشق - أبو رمانة',
            'phone' => '011223344'
        ]);


       // 6️⃣ إنشاء مدير المخبر (Lab Manager) - تعبئة الحقلين معاً
        $manager = User::create([
            'name' => 'د. أحمد العلي (مدير المخبر)',
            'email' => 'manager@medora.com',
            'password' => Hash::make('password1234@@'),
            'status' => 'active',
            'lab_id' => $lab->id // ⚡ الحقل القديم لضمان عمل أكوادك السابقة
        ]);
        $manager->assignRole('lab_manager');
        
        EmployeeProfile::create([
            'user_id' => $manager->id,
            'laboratory_id' => $lab->id, // الحقل الجديد للبروفايل
            'internal_employee_number' => 'EMP-MGR-01',
            'job_title' => 'manager',
            'specialization' => 'التحاليل الطبية المناعية'
        ]);


        // 7️⃣ إنشاء موظف الاستقبال (Receptionist) - تعبئة الحقلين معاً
        $receptionist = User::create([
            'name' => 'الآنسة سارة (موظفة الاستقبال)',
            'phone' => '0500000001',
            'email' => 'reception@medora.com',
            'password' => Hash::make('password1234@@'),
            'status' => 'active',
            'lab_id' => $lab->id // ⚡ الحقل القديم لضمان عمل أكوادك السابقة
        ]);
        $receptionist->assignRole('receptionist');

        EmployeeProfile::create([
            'user_id' => $receptionist->id,
            'laboratory_id' => $lab->id, // الحقل الجديد للبروفايل
            'internal_employee_number' => 'EMP-REC-02',
            'job_title' => 'receptionist',
            'specialization' => 'إدارة البيانات الصحية'
        ]);


        // 8️⃣ إنشاء المساعد المخبري (Lab Assistant) - تعبئة الحقلين معاً
        $assistant = User::create([
            'name' => 'الأستاذ فادي (المساعد المخبري)',
            'email' => 'assistant@medora.com',
            'password' => Hash::make('password1234@@'),
            'status' => 'active',
            'lab_id' => $lab->id // ⚡ الحقل القديم لضمان عمل أكوادك السابقة
        ]);
        $assistant->assignRole('lab_assistant');

        EmployeeProfile::create([
            'user_id' => $assistant->id,
            'laboratory_id' => $lab->id, // الحقل الجديد للبروفايل
            'internal_employee_number' => 'EMP-AST-03',
            'job_title' => 'assistant',
            'specialization' => 'سحب عينات الدم وفصلها'
        ]);


        $patient = User::create([
            'name' => 'خالد العتيبي',
            'email' => 'khaled@medora.com',
            'phone' => '0533333333', // 
            'password' => Hash::make('password1234@@'),
            'status' => 'active',
            'lab_id' => $lab->id
        ]);
        $patient->assignRole('patient');


        $patientUser = User::create([
            'name' => 'محمد بن عبد العزيز',
            'email' => 'm.abdulaziz@email.com',
            'phone' => '+966501234567',
            'password' => Hash::make('password'),
            'status' => 'active',
            'lab_id' => $lab->id
        ]);
        $patientUser->assignRole('patient');

        // ⚡ تأكدي من إنشاء البروفايل الملحق به لكي تظهر البيانات بالجدول كاملاً:
        \App\Models\PatientProfile::create([
            'user_id' => $patientUser->id,
            'gender' => 'male',
            'birth_date' => '1984-10-15',
            'emergency_phone' => '+966559876543',
            'address' => 'حي النخيل، الرياض، المملكة العربية السعودية',
            'medical_notes' => 'مريض سكري,يعاني من سيولة دم,فوبيا من الإبر'
        ]);



        
        /*
        |--------------------------------------------------------------------------
        | 🧪 شحن تحاليل المختبر الافتراضية (Lab Tests Seeder)
        |--------------------------------------------------------------------------
        */

        // جلب أول 10 تحاليل قياسية
        $masterTests = \App\Models\MasterTest::select('id')->take(10)->get();

        if ($masterTests->isNotEmpty() && isset($lab)) {
            $labTestsData = [];
            $now = now();

            // أسعار تشغيلية افتراضية بالليرة السورية
            $prices = [15000, 25000, 30000, 12000, 45000, 20000, 35000, 60000, 18000, 55000];

            foreach ($masterTests as $index => $masterTest) {
                $labTestsData[] = [
                    'lab_id'         => $lab->id, // ⚡ تم التأكيد الحاسم: الاسم هنا lab_id حصراً ومطابق للجدول
                    'master_test_id' => $masterTest->id,
                    'price'          => $prices[$index] ?? 25000,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }

            // حقن البيانات في قاعدة البيانات مباشرة مع تجاهل التكرار
            \Illuminate\Support\Facades\DB::table('lab_tests')->insertOrIgnore($labTestsData);
        }
    }
}