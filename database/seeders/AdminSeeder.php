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
        User::updateOrCreate([
            'name' => 'Rana Abu Arab',
            'email' => 'ranaabuarab2002@gmail.com.com',
            'password' => bcrypt('Rana1234@'),
            'role' => 'admin'
        ]);

    }
}
