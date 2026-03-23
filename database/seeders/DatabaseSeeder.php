<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // استدعاء ملف الأدوار والصلاحيات أولاً (ترتيب ضروري)
        $this->call([
            RolesAndPermissionsSeeder::class,
            TestCategorySeeder::class,
        ]);

        // الآن يمكنك إنشاء مستخدم وتجربة إعطاؤه دوراً فوراً إذا أردتِ
        $admin = User::factory()->create([
            'name' => 'Rana Admin',
            'email' => 'admin@medora.com',
            'password' => bcrypt('password1234@'),
        ]);

        // إعطاء الدور للمستخدم التجريبي
        $admin->assignRole('admin');
    }
}