<?php

namespace App\Services\LabManager;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LabPatientService
{
    /**
     * جلب إجمالي عدد المرضى الفريدين للمخبر
     */
    public function getTotalPatientsCount(int $labId): int
    {
        return DB::table('appointments')
            ->where('lab_id', $labId)
            ->distinct('user_id')
            ->count('user_id');
    }

    /**
     * جلب قائمة المرضى المسجلين بالمخبر مع حساب عدد فحوصاتهم وتاريخ آخر زيارة
     */
    public function getPaginatedPatients(int $labId, array $filters)
    {
        $query = DB::table('users')
            ->join('appointments', 'users.id', '=', 'appointments.user_id')
            ->leftJoin('patient_profiles', 'users.id', '=', 'patient_profiles.user_id') // ⚡ ربط جدول البروفايل لجلب الجنس وتاريخ الميلاد
            ->where('appointments.lab_id', $labId)
            ->select(
                'users.id',
                'users.name',
                'users.phone',
                'patient_profiles.gender',     // جلب الجنس من جدول البروفايل
                'patient_profiles.birth_date', // جلب تاريخ الميلاد من جدول البروفايل
                DB::raw('MAX(appointments.created_at) as last_visit_date'),
                DB::raw('COUNT(appointments.id) as total_appointments_count')
            )
            ->groupBy('users.id', 'users.name', 'users.phone', 'patient_profiles.gender', 'patient_profiles.birth_date');

        // تطبيق فلتر البحث بالاسم أو الهاتف
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('users.name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('users.phone', 'like', '%' . $filters['search'] . '%');
            });
        }

        // تطبيق فلتر الجنس
        if (!empty($filters['gender']) && $filters['gender'] !== 'all') {
            $query->where('patient_profiles.gender', $filters['gender']);
        }

        return $query->orderBy('last_visit_date', 'desc')->paginate(4);
    }

    /**
     * حساب إحصائية التوزيع العمري للمرضى حقيقياً بالاعتماد على بروفايل المريض
     */
    public function getAgeDistribution(int $labId): array
    {
        $birthDates = DB::table('patient_profiles')
            ->join('appointments', 'patient_profiles.user_id', '=', 'appointments.user_id')
            ->where('appointments.lab_id', $labId)
            ->select('patient_profiles.birth_date')
            ->distinct('patient_profiles.user_id')
            ->pluck('birth_date');

        $total = $birthDates->count();
        if ($total == 0) return ['age_18_30' => '0%', 'age_31_50' => '0%', 'above_50' => '0%'];

        $group1 = 0; // 18-30
        $group2 = 0; // 31-50
        $group3 = 0; // فوق 50

        foreach ($birthDates as $date) {
            if (empty($date)) continue;
            
            // حساب العمر ديناميكياً بالسنين
            $age = Carbon::parse($date)->age;

            if ($age >= 18 && $age <= 30) {
                $group1++;
            } elseif ($age >= 31 && $age <= 50) {
                $group2++;
            } elseif ($age > 50) {
                $group3++;
            }
        }

        return [
            'age_18_30' => round(($group1 / $total) * 100) . '%',
            'age_31_50' => round(($group2 / $total) * 100) . '%',
            'above_50'  => round(($group3 / $total) * 100) . '%',
        ];
    }
}