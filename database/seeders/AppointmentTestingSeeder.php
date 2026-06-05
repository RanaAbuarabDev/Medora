<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\Laboratory;
use App\Models\User;
use App\Models\LabTest;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AppointmentTestingSeeder extends Seeder
{
    /**
     * تشغيل السييدر لبناء بيئة اختبار متكاملة
     */
    public function run(): void
    {
        // 1. جلب أو إنشاء مختبر افتراضي لتتم عليه المواعيد
        $lab = Laboratory::first() ?? Laboratory::create([
            'name' => 'مختبر ميدورا الرئيسي',
            'slot_interval' => 15
        ]);

        // 2. جلب أو إنشاء المستخدمين بالأدوار المطلوبة
        $patient = User::firstOrCreate(
            ['email' => 'patient@medora.com'],
            ['name' => 'أحمد المريض التجريبي', 'password' => bcrypt('password')]
        );
        $patient->assignRole('patient'); 

        $assistant = User::firstOrCreate(
            ['email' => 'assistant@medora.com'],
            ['name' => 'سامر المساعد المخبري', 'password' => bcrypt('password'), 'lab_id' => $lab->id]
        );
        $assistant->assignRole('lab_assistant');

        // 3. جلب بعض التحاليل الموجودة بجدولك لحشوها في جدول الربط
        $testIds = LabTest::pluck('id')->take(3)->toArray();
        if (empty($testIds)) {
            $test1 = LabTest::create(['name' => 'CBC', 'price' => 5000, 'test_category_id' => 1]);
            $test2 = LabTest::create(['name' => 'PT', 'price' => 7000, 'test_category_id' => 1]);
            $testIds = [$test1->id, $test2->id];
        }

        // --- [الحالة 1]: موعد جديد تماماً (Pending) بانتظار موظف الاستقبال ---
        $appPending = Appointment::create([
            'user_id' => $patient->id,
            'lab_id' => $lab->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:15:00',
            'status' => 'pending',
            'sample_code' => 'LAB-' . strtoupper(Str::random(6)),
            // 🚫 تم حذف سطر master_test_id من هنا بنجاح
        ]);
        $this->attachTestsAndInvoice($appPending, $testIds);

        // --- [الحالة 2]: موعد تم تأكيده بواسطة الاستقبال (Waiting) وهو في صالة الانتظار عند المساعد ---
        $appWaiting = Appointment::create([
            'user_id' => $patient->id,
            'lab_id' => $lab->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'start_time' => '11:00:00',
            'end_time' => '11:15:00',
            'status' => 'waiting',
            'sample_code' => 'LAB-' . strtoupper(Str::random(6)),
            // 🚫 تم حذف سطر master_test_id من هنا بنجاح
        ]);
        $this->attachTestsAndInvoice($appWaiting, $testIds);

        // --- [الحالة 3]: موعد منتهي ومكتمل النتائج (Completed) لتجربة واجهات المريض والتقارير ---
        $appCompleted = Appointment::create([
            'user_id' => $patient->id,
            'lab_id' => $lab->id,
            'appointment_date' => Carbon::yesterday()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:15:00',
            'status' => 'completed',
            'sample_code' => 'LAB-' . strtoupper(Str::random(6)),
            // 🚫 تم حذف سطر master_test_id من هنا بنجاح
        ]);
        
        // في الموعد المكتمل، نحشو قيم نتائج تجريبية بجدول الربط مباشرة
        $syncData = [];
        foreach ($testIds as $id) {
            $syncData[$id] = [
                'result_value' => '14.5',
                'status' => 'completed',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }
        $appCompleted->labTests()->attach($syncData);
        
        // إنشاء فاتورة مدفوعة للموعد المكتمل
        Invoice::create([
            'appointment_id' => $appCompleted->id,
            'total_amount' => LabTest::whereIn('id', $testIds)->sum('price'),
            'amount_paid' => LabTest::whereIn('id', $testIds)->sum('price'),
            'payment_status' => 'paid'
        ]);

        $this->command->info('🎉 تم بناء بيئة المواعيد التجريبية بنجاح عارم لجميع الحالات!');
    }

    /**
     * دالة مساعدة لربط التحاليل المعلقة وإنشاء الفاتورة غير المدفوعة
     */
    private function attachTestsAndInvoice($appointment, array $testIds)
    {
        $syncData = [];
        foreach ($testIds as $id) {
            $syncData[$id] = [
                'result_value' => null,
                'status' => 'pending',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }
        $appointment->labTests()->attach($syncData);

        Invoice::create([
            'appointment_id' => $appointment->id,
            'total_amount' => LabTest::whereIn('id', $testIds)->sum('price'),
            'amount_paid' => 0,
            'payment_status' => 'unpaid'
        ]);
    }
}