<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Jalankan RoleSeeder dulu
        $this->call(RoleSeeder::class);

        // Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
            ]
        );

        $superAdmin->syncRoles(['super_admin']);

        // Admin Cabang
        $adminCabang = User::updateOrCreate(
            ['email' => 'cabang@gmail.com'],
            [
                'name' => 'Admin Cabang',
                'password' => Hash::make('password123'),
            ]
        );

        $adminCabang->syncRoles(['admin_cabang']);

        // Tutor
        $tutor = User::updateOrCreate(
            ['email' => 'tutor@gmail.com'],
            [
                'name' => 'Tutor',
                'password' => Hash::make('password123'),
            ]
        );

        $tutor->syncRoles(['tutor']);

        // Siswa
        $siswa = User::updateOrCreate(
            ['email' => 'siswa@gmail.com'],
            [
                'name' => 'Siswa',
                'password' => Hash::make('password123'),
            ]
        );

        $siswa->syncRoles(['siswa']);
    }
}