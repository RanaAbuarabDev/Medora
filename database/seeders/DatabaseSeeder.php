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
        
        $this->call([
            RolesAndPermissionsSeeder::class,
            TestCategorySeeder::class,
            MasterTestSeeder::class
        ]);

        
        $admin = User::factory()->create([
            'name' => 'Rana Admin',
            'email' => 'admin@medora.com',
            'password' => bcrypt('password1234@'),
        ]);

        
        $admin->assignRole('admin');
    }
}