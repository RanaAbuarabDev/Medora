<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Rana Abu Arab',
            'email' => 'abuarabrana@gmail.com',
            'password' => bcrypt('12341234'),
            'role' => 'admin'
        ]);

    }
}
