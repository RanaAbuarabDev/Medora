<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'CreateAdmin';

    /**
     * The console command description.
     */
    protected $description = 'Create a new admin user interactively';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('Enter admin name');
        $email = $this->ask('Enter admin email');
        $password = $this->secret('Enter admin password');

        if (User::where('email', $email)->exists()) {
            $this->error('User with this email already exists.');
            return Command::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password), 
            //'role' => 'admin',
            'lab_id'=> null,
        ]);

        $user->assignRole(\Spatie\Permission\Models\Role::findByName('admin', 'api'));
        $this->info('Admin created successfully.');
        return Command::SUCCESS;
    }
}
