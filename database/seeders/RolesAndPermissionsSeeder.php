<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'super_admin']);
        Role::create(['name' => 'lab_manager']);
        Role::create(['name' => 'receptionist']);
        Role::create(['name' => 'lab_assistant']);
        Role::create(['name' => 'patient']);


        Permission::create(['name' => 'manage_laboratories']);
        $adminRole = Role::findByName('super_admin');
        $adminRole->givePermissionTo('manage_laboratories');
    }
}
