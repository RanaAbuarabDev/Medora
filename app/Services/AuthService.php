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
            'user'  => $user,
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
        // OTP فقط للمرضى
        $this->otpService->send($user->email, 'registration');
    }

    public function setupNewLab(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. إنشاء المخبر
            $lab = Laboratory::create([
                'name' => $data['lab_name'],
                'address' => $data['address'],
                'is_active' => true, // أو false بانتظار تفعيل المنصة
            ]);

            // 2. إنشاء المدير
            $manager = User::create([
                'name' => $data['manager_name'],
                'email' => $data['manager_email'],
                'password' => Hash::make($data['manager_password']),
                'lab_id' => $lab->id, // ربط المدير بمخبره
            ]);

            // 3. منح الدور (Spatie)
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
}
