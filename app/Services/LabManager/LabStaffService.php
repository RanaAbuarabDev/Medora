<?php

namespace App\Services\LabManager;

use App\Models\User;

class LabStaffService
{
    /**
     * جلب إجمالي عدد الموظفين (استقبال وفنيين فقط) المرتبطين بالمخبر الحالي
     */
    public function getStaffCount(int $labId): int
    {
        // ⚡ تعديل لحساب الموظفين الفعليين فقط دون المدير أو المرضى
        return User::where('lab_id', $labId)
            ->role(['receptionist', 'lab_assistant'])
            ->count();
    }

    /**
     * جلب قائمة الموظفين المفلترة والـ Paginated للمخبر (استقبال وفنيين فقط)
     */
    public function getPaginatedStaff(int $labId, array $filters)
    {
        // ⚡ بناء الاستعلام وحصره فقط بالأدوار المطلوبة في الواجهة
        $query = User::where('lab_id', $labId)
            ->role(['receptionist', 'lab_assistant'])
            ->select('id', 'name', 'email', 'phone', 'created_at');

        // 1. فلتر البحث بالاسم أو البريد الإلكتروني
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        // 2. فلتر الدور الوظيفي عند تحديده من القائمة المنسدلة
        if (!empty($filters['role']) && $filters['role'] !== 'all') {
            $query->role($filters['role']);
        }

        // ترتيب الموظفين وعرض 3 أسطر بالصفحة كما بالتصميم
        return $query->orderBy('created_at', 'desc')->paginate(3);
    }

    public function getStaffById(int $labId, int $staffId): ?User
    {
        return User::where('lab_id', $labId)
            ->role(['receptionist', 'lab_assistant'])
            ->where('id', $staffId)
            ->first();
    }

    /**
     * تحديث بيانات الموظف ودوره الوظيفي
     */
    public function updateStaff(User $employee, array $data): User
    {
        // تحديث الحقول الأساسية المرسلة
        $employee->update([
            'name'  => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? $employee->phone,
        ]);

        // تحديث الدور الوظيفي باستخدام Spatie Permissions
        if (!empty($data['role'])) {
            $employee->syncRoles([$data['role']]);
        }

        return $employee;
    }

    
    public function toggleBlock(User $employee): User
    {
        
        $employee->is_blocked = ($employee->is_blocked == 1) ? 0 : 1;
        $employee->save();
        return $employee;
    }


    
    public function createStaff(int $labId, array $data): User
    {
     
        $employee = User::create([
            'lab_id'     => $labId,
            'name'       => $data['name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'],
            'password'   => bcrypt($data['password']), 
            'is_blocked' => 0, 
        ]);

    
        $employee->assignRole($data['role']);

        return $employee;
    }
}