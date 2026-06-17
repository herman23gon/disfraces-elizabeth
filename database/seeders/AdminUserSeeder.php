<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Herman Colque',
            'email' => 'herman.gcp@gmail.com',
            'password' => Hash::make('Agua2025'),
        ]);

        $admin->assignRole('administrador');
    }
}

