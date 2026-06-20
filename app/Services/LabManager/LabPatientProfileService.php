<?php

namespace App\Services\LabManager;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LabPatientProfileService
{
    /**
     * جلب البيانات الشخصية والطبية الأساسية للمريض
     */
    public function getPatientDetails(int $patientId, int $labId)
    {
        return DB::table('users')
            ->leftJoin('patient_profiles', 'users.id', '=', 'patient_profiles.user_id')
            ->where('users.id', $patientId)
            ->select(
                'users.id',
                'users.name',
                'users.phone',
                'patient_profiles.gender',
                'patient_profiles.birth_date',
                'patient_profiles.blood_group',
                'patient_profiles.medical_notes',
                DB::raw("(SELECT MAX(created_at) FROM appointments WHERE user_id = users.id AND lab_id = {$labId}) as last_visit_date")
            )
            ->first();
    }

    /**
     * جلب سجل التحاليل الطبية والفحوصات الخاصة بالمريض داخل هذا المخبر بالتحديد
     */
    public function getPatientTestsHistory(int $patientId, int $labId)
    {
        return DB::table('appointment_lab_test')
            ->join('appointments', 'appointment_lab_test.appointment_id', '=', 'appointments.id')
            ->join('lab_tests', 'appointment_lab_test.lab_test_id', '=', 'lab_tests.id')
            ->join('master_tests', 'lab_tests.master_test_id', '=', 'master_tests.id')
            ->where('appointments.user_id', $patientId)
            ->where('appointments.lab_id', $labId)
            ->select(
                'appointment_lab_test.id as pivot_id',
                'master_tests.name as test_name',
                'master_tests.short_name as test_short_name',
                'appointments.created_at as test_date',
                'appointments.status as test_status'
            )
            ->orderBy('appointments.created_at', 'desc')
            ->get();
    }
}