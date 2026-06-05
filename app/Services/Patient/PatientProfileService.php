<?php

namespace App\Services\Patient;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PatientProfileService
{
    /**
     * 1. جلب بيانات البروفايل بالكامل مشحونة بالبيانات الطبية
     */
    /**
     * 1. جلب بيانات البروفايل بالكامل مشحونة بالبيانات الطبية (نسخة آمنة ومحمية)
     */
    public function getProfileData()
    {
        // جلب المستخدم الحالي مع البروفايل الخاص به
        $user = auth()->user()->load('patientProfile');
        $profile = $user->patientProfile; // تخزينه في متغير للفحص

        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'phone'         => $user->phone ?? $user->phone_number ?? '',
            'email'         => $user->email,
            'avatar_url'    => $user->avatar ? asset('storage/' . $user->avatar) : null,
            
            // 👈 حماية الحقول هنا باستخدام الاختصار الشرطي لمنع خطأ الـ null
            'birth_date'    => $profile->birth_date ?? '',
            'gender'        => $profile && $profile->gender === 'female' ? 'أنثى' : 'ذكر',
            'medical_notes' => $profile->medical_notes ?? 'لا يوجد ملاحظات طبية.',
        ];
    }

    /**
     * 2. تحديث بيانات البروفايل، الصورة، وكلمة المرور بأمان
     */
    /**
     * 2. تحديث بيانات البروفايل، الصورة، وكلمة المرور بأمان (النسخة المحمية)
     */
    public function updateProfile(array $data)
    {
        $user = auth()->user();

        // أ) التحقق من كلمة المرور وتحديثها إن طلبت الواجهة ذلك
        if (!empty($data['new_password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                throw new Exception('كلمة المرور الحالية غير صحيحة.');
            }
            $user->password = Hash::make($data['new_password']);
        }

        // ب) معالجة رفع الصورة الشخصية (Avatar) وحذف القديمة إن وجدت
        if (isset($data['avatar']) && $data['avatar']->isValid()) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $data['avatar']->store('avatars', 'public');
        }

        // جـ) تحديث بيانات جدول المستخدمين الأساسي بشكل آمن (👈 التعديل هنا)
        $user->name = $data['name'] ?? $user->name;
        $user->email = $data['email'] ?? $user->email; // إذا لم يرسل الإيميل، يحتفظ بالقديم دون أخطاء
        
        if (isset($data['phone'])) {
            $user->phone = $data['phone'];
        }
        $user->save();

        // د) تحويل الجنس للغة الإنجليزية للتخزين القياسي
        $gender = isset($data['gender']) && in_array($data['gender'], ['أنثى', 'female']) ? 'female' : 'male';

        // هـ) تحديث بيانات جدول بروفايل المريض الإضافية بشكل آمن (👈 والتعديل هنا)
        $user->patientProfile()->updateOrCreate(
            ['user_id' => $user->id], // شرط البحث
            [
                'birth_date'    => $data['birth_date'] ?? ($user->patientProfile->birth_date ?? null),
                'gender'        => isset($data['gender']) ? $gender : ($user->patientProfile->gender ?? 'male'),
                'medical_notes' => $data['medical_notes'] ?? ($user->patientProfile->medical_notes ?? null),
            ]
        );

        return $this->getProfileData(); // إعادة البيانات المحدثة بالكامل فوراً للواجهة
    }
}