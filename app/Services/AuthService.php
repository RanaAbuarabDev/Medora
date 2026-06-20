<?php

namespace App\Services;

use App\Models\Laboratory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Services\OtpService;

class AuthService
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function login(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new \Exception('بيانات الاعتماد المدخلة غير صحيحة', 401);
        }

        // ⚡ منع المستخدم المحظور من تسجيل الدخول وإرجاع استثناء مخصص
        if ($user->is_blocked) {
            throw new \Exception('تم حظر هذا الحساب من قبل إدارة المختبر.', 403);
        }

        return [
            'token' => $user->createToken('api-token')->plainTextToken,
            'user'  => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'lab_id' => $user->lab_id,
                // ⚡ تأكيد جلب الأدوار كـ Array (مثال: ["lab_manager"] أو ["receptionist"])
                'roles'  => $user->getRoleNames(), 
            ],
        ];
    }

    public function registerPatient(array $data): void
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'email_verified_at' => null
        ]);

        $user->assignRole('patient');
        
        $this->otpService->send($user->email, 'registration');
    }

    public function setupNewLab(array $data)
    {
        return DB::transaction(function () use ($data) {
            $licenseNumber = $this->generateLicenseNumber();

            $lab = Laboratory::create([
                'name' => $data['lab_name'],
                'address' => $data['address'],
                'license_number' => $licenseNumber, 
                'status' => 'pending', 
            ]);

            $manager = User::create([
                'name' => $data['manager_name'],
                'email' => $data['manager_email'],
                'password' => Hash::make($data['manager_password']),
                'lab_id' => $lab->id, 
            ]);

            $manager->assignRole('lab_manager');

            return [
                'lab' => $lab,
                'manager' => $manager
            ];
        });
    }

    public function addLabAssistant(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'lab_id'   => auth()->user()->lab_id,
            ]);

            $user->assignRole('lab_assistant');
            
            return [
                'user'  => $user,
                'token' => $user->createToken('api-token')->plainTextToken,
            ];
        });
    }

    public function addReceptionist(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'lab_id'   => auth()->user()->lab_id,
            ]);

            $user->assignRole('receptionist');

            return [
                'user'  => $user,
                'token' => $user->createToken('api-token')->plainTextToken,
            ];
        });
    }

    private function generateLicenseNumber(): string
    {
        do {
            $licenseNumber = 'LAB-' . rand(1000, 9999);
        } while (Laboratory::where('license_number', $licenseNumber)->exists());

        return $licenseNumber;
    }

    public function deleteLaboratory(int $labId)
    {
        $lab = Laboratory::find($labId);
        if (!$lab) {
            throw new \Exception('المخبر المطلوب غير موجود بالنظام.');
        }

        DB::beginTransaction();
        try {
            User::where('lab_id', $labId)->delete();
            $lab->delete();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('حدث خطأ أثناء إزالة السجلات من قاعدة البيانات: ' . $e->getMessage());
        }
    }
}