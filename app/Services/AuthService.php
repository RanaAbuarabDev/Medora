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
            throw new \Exception('Invalid credentials', 401);
        }

        return [
            'token' => $user->createToken('api-token')->plainTextToken,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(), 
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
        // 1. التحقق من وجود المخبر أولاً
        $lab = Laboratory::find($labId);
        if (!$lab) {
            throw new \Exception('المخبر المطلوب غير موجود بالنظام.');
        }

        DB::beginTransaction();
        try {
            // 2. حذف أو تعطيل كافة المستخدمين (المدير والمساعدين) المرتبطين بهذا المخبر
            // يمكنكِ استخدام delete() للحذف النهائي، أو حظرهم حسب سياسة التخرج
            User::where('lab_id', $labId)->delete();

            // 3. حذف المخبر نفسه
            $lab->delete();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('حدث خطأ أثناء إزالة السجلات من قاعدة البيانات: ' . $e->getMessage());
        }
    }
}
