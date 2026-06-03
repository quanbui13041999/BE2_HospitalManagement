<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [];

        // 1 Admin
        $users[] = [
            'full_name' => 'System Administrator',
            'email' => 'admin@hospital.com',
            'password' => Hash::make('123456'),
            'phone' => '0900000001',
            'address' => 'Ho Chi Minh City',
            'gender' => 'Male',
            'role_id' => 1,
            'status' => true,
            'avatar_url' => null,
            'date_of_birth' => '1990-01-01',
            'created_at' => now(),
        ];

        // 12 Doctors
        for ($i = 1; $i <= 12; $i++) {
            $users[] = [
                'full_name' => "Doctor $i",
                'email' => "doctor$i@hospital.com",
                'password' => Hash::make('123456'),
                'phone' => '091' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'address' => 'Ho Chi Minh City',
                'gender' => $i % 2 == 0 ? 'Female' : 'Male',
                'role_id' => 2,
                'status' => true,
                'avatar_url' => null,
                'date_of_birth' => '1985-01-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'created_at' => now(),
            ];
        }

        // 10 Patients
        for ($i = 1; $i <= 10; $i++) {
            $users[] = [
                'full_name' => "Patient $i",
                'email' => "patient$i@gmail.com",
                'password' => Hash::make('123456'),
                'phone' => '092' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'address' => 'Ho Chi Minh City',
                'gender' => $i % 2 == 0 ? 'Female' : 'Male',
                'role_id' => 3,
                'status' => true,
                'avatar_url' => null,
                'date_of_birth' => '2000-01-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'created_at' => now(),
            ];
        }

        DB::table('users')->insert($users);
    }
}