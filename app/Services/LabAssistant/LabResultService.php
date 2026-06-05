<?php

namespace App\Services\LabAssistant;

use App\Models\Appointment;
use App\Models\AppointmentLabTest;
use Exception;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LabResultService
{
    /**
     * 1. البحث عن عينة بجلب معلومات المريض والتحاليل من جدول المختبر والجدول الأم
     */
    /**
     * 1. البحث عن عينة بجلب معلومات المريض والتحاليل من جدول المختبر والجدول الأم
     */
    /**
     * 1. البحث عن عينة بجلب معلومات المريض والتحاليل تابعة للمختبر الحالي حصراً
     */
    public function searchBySampleCode(string $sampleCode)
    {
        // جلب معرف المختبر الحالي للمستخدم المسجل دخوله
        $currentLabId = auth()->user()->lab_id ?? null;

        $appointment = Appointment::where(function($query) use ($sampleCode) {
                $query->where('sample_code', $sampleCode)
                      ->orWhere('id', str_replace('#LAB-', '', $sampleCode));
            })
            ->where('lab_id', $currentLabId) // 👈 هذا السطر السحري يمنع المساعد من رؤية عينات المختبرات الأخرى عند البحث أو الضغط على العين
            ->with(['patientProfile.user', 'labTests.masterTest'])
            ->first();

        if (!$appointment) {
            throw new Exception('عذراً، لم يتم العثور على أي عينة مسجلة بهذا الرقم في هذا المختبر.');
        }

        if (!in_array($appointment->status, ['waiting', 'in_progress', 'completed'])) {
            throw new Exception('لا يمكن إدخال أو عرض نتائج لعينة لا تزال بحالة معلقة.');
        }

        return [
            'appointment_id' => $appointment->id,
            'sample_code'    => $appointment->sample_code,
            'patient_name'   => $appointment->patientProfile->user->name ?? 'مريض غير معروف',
            'patient_age'    => '24 سنة', 
            'patient_gender' => 'أنثى',
            'date'           => $appointment->appointment_date,
            'tests'          => $appointment->labTests->map(function ($labTest) {
                return [
                    'appointment_test_id' => $labTest->pivot->id, 
                    'test_name'           => $labTest->masterTest->name ?? $labTest->name,
                    'result_value'        => $labTest->pivot->result_value, 
                    'unit'                => $labTest->masterTest->unit ?? 'g/dL',
                    'min_value'           => $labTest->masterTest->min_value ?? 12.0,
                    'max_value'           => $labTest->masterTest->max_value ?? 16.0,
                    'status'              => $labTest->pivot->status ?? 'pending',
                ];
            }),
            'is_completed' => $appointment->status === 'completed'
        ];
    }

    /**
     * 2. حفظ وتخزين النتائج في جدول الربط
     */
    public function saveResults(int $appointmentId, array $testsData, string $actionType)
    {
        $appointment = Appointment::findOrFail($appointmentId);

        DB::beginTransaction();
        try {
            foreach ($testsData as $testItem) {
                // التحديث المباشر في جدول الربط باستخدام المعرف الفردي له لضمان عدم التداخل
                $labTestLink = AppointmentLabTest::find($testItem['appointment_test_id']);

                if ($labTestLink) {
                    $labTestLink->update([
                        'result_value' => $testItem['result_value'] ?? null,
                        'status'       => $actionType === 'complete' ? 'completed' : 'pending'
                    ]);
                }
            }

            // تحديث حالة الموعد الكلية
            if ($actionType === 'complete') {
                $appointment->update(['status' => 'completed']);

               
                $fullAppointmentData = Appointment::with(['patientProfile.user', 'labTests.masterTest', 'lab'])
                    ->find($appointmentId);

                $patientUser = $fullAppointmentData->patientProfile->user ?? null;
                
                if ($patientUser) {
                    
                    $patientUser->notify(new \App\Notifications\TestResultsReadyNotification($fullAppointmentData));
                }
                
            } else {
                $appointment->update(['status' => 'in_progress']);
            }

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('فشل في حفظ البيانات: ' . $e->getMessage());
        }
    }


    
    /**
     * جلب قائمة التقارير والنتائج مع الفلترة والبحث والتقسيم لصفحات
     */
    public function getPaginatedResults(array $filters)
    {

        $currentLabId = auth()->user()->lab_id ?? null;

        // بناء الاستعلام الأساسي مع العلاقات بـ Eager Loading لمنع تكرار الـ Queries
        $query = Appointment::with(['patientProfile.user', 'labTests.masterTest'])
            ->where('lab_id', $currentLabId)
            ->whereIn('status', ['in_progress', 'completed']); // نجلب فقط العينات المسودات والمرفوعة

        // 1. تطبيق الفلترة بالبحث الذكي (اسم المريض، كود العينة، أو اسم التحليل الأم)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('sample_code', 'LIKE', "%{$search}%")
                ->orWhere('id', 'LIKE', "%{$search}%")
                ->orWhereHas('patientProfile.user', function ($uQ) use ($search) {
                    $uQ->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('labTests.masterTest', function ($mTQ) use ($search) {
                    $mTQ->where('name', 'LIKE', "%{$search}%");
                });
            });
        }

        // 2. تطبيق الفلترة بالتاريخ (تطابقاً مع خيارات الواجهة)
        if (!empty($filters['date_range'])) {
            switch ($filters['date_range']) {
                case 'today':
                    $query->whereDate('appointment_date', Carbon::today());
                    break;
                case 'last_7_days':
                    $query->whereDate('appointment_date', '>=', Carbon::now()->subDays(7));
                    break;
                case 'last_30_days':
                    $query->whereDate('appointment_date', '>=', Carbon::now()->subDays(30));
                    break;
            }
        }

        // 3. تطبيق الفلترة بحالة التقرير (مسودة أو مرفوع فقط)
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'draft') {
                $query->where('status', 'in_progress'); 
            } elseif ($filters['status'] === 'completed') {
                $query->where('status', 'completed'); 
            }
        }

        // ترتيب السجلات من الأحدث للأقدم بناءً على تاريخ الموعد لراحة الاستخدام
        $paginated = $query->orderBy('appointment_date', 'desc')->paginate(10);

        // تنسيق المخرجات لتطابق الجداول والأعمدة في تصميم الواجهة بالملّي
        $formattedItems = $paginated->map(function ($app) {
            return [
                'id'               => $app->id,
                'patient_name'     => $app->patientProfile->user->name ?? 'مريض غير معروف',
                'patient_initials' => $this->getInitials($app->patientProfile->user->name ?? 'م'),
                'sample_code'      => $app->sample_code ?? '#SMP-' . $app->id,
                'test_types'       => $app->labTests->map(function ($lt) {
                    return $lt->masterTest->name ?? $lt->name;
                })->implode(' - '),
                // تنسيق التاريخ ليكون مقروءاً وجميلاً بالواجهة العربية
                'date'             => Carbon::parse($app->appointment_date)->translatedFormat('d أكتوبر Y'), 
                'raw_status'       => $app->status,
                'status_text'      => $app->status === 'completed' ? 'مرفوع' : 'مسودة', 
            ];
        });

        return [
            'results' => $formattedItems,
            'pagination' => [
                'total'        => $paginated->total(),
                'count'        => $paginated->count(),
                'per_page'     => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
            ]
        ];
    }

    
    private function getInitials($name)
    {
        $words = explode(' ', $name);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= mb_substr($w, 0, 1);
        }
        return $initials;
    }
}