<?php

namespace App\Services\LabAssistant;

use App\Models\Appointment;
use Carbon\Carbon;

class LabAssistantDashboardService
{
    /**
     * جلب بيانات لوحة التحكم بناءً على دورة حياة الموعد الجديدة
     */
    public function getDashboardData()
    {
        $today = Carbon::today();

        // 1. حساب العدادات العلوية لليوم الحالي من جدول الـ appointments مباشرة
        $totalToday = Appointment::whereDate('appointment_date', $today)
            ->whereIn('status', ['waiting', 'in_progress', 'completed'])
            ->count();

        $inProgress = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'in_progress')
            ->count();

        $completed = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'completed')
            ->count();

        $uploaded = $completed; 

        // 2. الاستعلام عن عينات بانتظار المعالجة (الجدول السفلي)
        //  تم تصحيح العلاقات هنا لتجلب المستخدم والتحاليل مع جدولها الأم بشكل متداخل وسليم
        $waitingAppointments = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'waiting')
            ->with(['patientProfile.user', 'labTests.masterTest']) 
            ->orderBy('created_at', 'asc')
            ->get();

        // تنسيق البيانات لتطابق تصميم الواجهة بالملّي
        $formattedSamples = $waitingAppointments->map(function ($app) {
            return [
                'id' => $app->id,
                'sample_code' => $app->sample_code ?? '#LAB-' . $app->id,
                'patient_name' => $app->patientProfile->user->name ?? 'مريض غير معروف',
                'patient_initials' => $this->getInitials($app->patientProfile->user->name ?? 'م'),
                // دمج أسماء التحاليل المطلوبة للمريض بنص واحد مع الحماية من القيم الفارغة
                'test_types' => $app->labTests->map(function($lt) {
                    return $lt->masterTest->name ?? $lt->name;
                })->implode(' - '),
                'status' => 'waiting', 
                'time' => $app->start_time ? Carbon::parse($app->start_time)->format('g:i أ') : '10:30 ص',
            ];
        });

        return [
            'statistics' => [
                'total_today_tests' => $totalToday,
                'in_progress_tests' => $inProgress,
                'completed_tests'   => $completed,
                'uploaded_results'  => $uploaded,
            ],
            'pending_samples' => $formattedSamples
        ];
    }

    /**
     * عملية حركية: تغيير حالة الموعد عند الضغط على زر "بدء التحليل"
     */
    public function startAnalysis(int $appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);

        if ($appointment->status !== 'waiting') {
            throw new \Exception('لا يمكن بدء التحليل لعينة لم يثبت حضور صاحبها بعد أو بدأت مسبقاً.');
        }

        $appointment->update([
            'status' => 'in_progress'
        ]);

        return true;
    }

    /**
     * توليد اختصار الحروف للافتار (س ع)
     */
    private function getInitials($name)
    {
        $words = explode(' ', $name);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= mb_substr($w, 0, 1);
        }
        return $initials;
    }

    /**
     * جلب مواعيد اليوم كاملة مع إمكانية الفلترة حسب التبويبات (Tabs)
     */
    public function getTodayAppointments($filterStatus = null)
    {
        $today = Carbon::today();

        //  تصحيح العلاقات هنا أيضاً لمنع انهيار السيرفر
        $query = Appointment::whereDate('appointment_date', $today)
            ->with(['patientProfile.user', 'labTests.masterTest']) // 👈 أعدناها patientProfile.user
            ->orderBy('id', 'asc');

        if ($filterStatus && in_array($filterStatus, ['waiting', 'in_progress', 'completed', 'cancelled'])) {
            $query->where('status', $filterStatus);
        }

        $appointments = $query->get();

        $totalTodayCount = Appointment::whereDate('appointment_date', $today)->count();
        $completedCount  = Appointment::whereDate('appointment_date', $today)->where('status', 'completed')->count();

        $formattedAppointments = $appointments->map(function ($app) {
            return [
                'id'               => $app->id,
                'sample_code'      => $app->sample_code ?? '#LAB-' . $app->id,
                'patient_name'     => $app->patientProfile->user->name ?? 'مريض غير معروف',
                'patient_initials' => $this->getInitials($app->patientProfile->user->name ?? 'م'),
                'test_types'       => $app->labTests->map(function($lt) {
                    return $lt->masterTest->name ?? $lt->name;
                })->implode(' - '),
                'raw_status'       => $app->status,
                'status_text'      => $this->translateStatus($app->status),
                'time'             => $app->start_time ? Carbon::parse($app->start_time)->format('g:i أ') : '09:00 صباحاً',
            ];
        });

        return [
            'meta' => [
                'total_statuses' => $totalTodayCount,
                'completed_count' => $completedCount,
            ],
            'appointments' => $formattedAppointments
        ];
    }

    /**
     * دالة مساعدة لترجمة الحالات لتبسيط العمل على الفرونت إند بالتلوين
     */
    private function translateStatus($status)
    {
        $translations = [
            'pending'     => 'معلق',
            'waiting'     => 'قيد الانتظار',
            'in_progress' => 'قيد التحليل',
            'completed'   => 'مكتمل',
            'cancelled'   => 'ملغي',
        ];
        return $translations[$status] ?? $status;
    }
}