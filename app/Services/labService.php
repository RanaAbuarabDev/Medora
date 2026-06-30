<?php


namespace App\Services;

use App\Models\Laboratory;
use App\Models\MasterTest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class labService
{

    public function createNewLabWithManager(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. إنشاء المختبر أولاً
            $lab = Laboratory::create([
                'name' => $data['lab_name'],
                'address' => $data['address'],
                'logo'=> $data['logo'],
                'phone'=>$data['phone'],
            ]);

            // 2. إنشاء المدير وربطه بالـ lab_id
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'lab_id' => $lab->id, // الربط هنا!
            ]);

            // 3. إعطاء الدور باستخدام Spatie
            $user->assignRole('lab_manager');

            return $lab;
        });

    }

    
      
       
    public function searchTests(string $searchTerm)
    {
        return MasterTest::where(function ($query) use ($searchTerm) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%')
                ->orWhere('short_name', 'LIKE', '%' . $searchTerm . '%');
        })
        ->with('Category') // شحن الفئة لتعود البيانات كاملة للمريض
        ->latest()
        ->get();
    }
    


}