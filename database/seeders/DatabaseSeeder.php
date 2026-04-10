<?php

namespace Database\Seeders;

use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Jalankan RoleSeeder dulu
        $this->call(RoleSeeder::class);

        if (Schema::hasTable('settings')) {
            $this->call(WhatsappSettingsSeeder::class);
            $this->call(SettingSeeder::class);
        }

        if (Schema::hasTable('mata_pelajarans')) {
            foreach (
                [
                    ['nama' => 'Matematika', 'kode' => 'MTK'],
                    ['nama' => 'Bahasa Indonesia', 'kode' => 'BIND'],
                    ['nama' => 'Bahasa Inggris', 'kode' => 'BING'],
                    ['nama' => 'Fisika', 'kode' => 'FIS'],
                    ['nama' => 'Kimia', 'kode' => 'KIM'],
                    ['nama' => 'Biologi', 'kode' => 'BIO'],
                ] as $mp
            ) {
                MataPelajaran::query()->firstOrCreate(
                    ['nama' => $mp['nama']],
                    ['kode' => $mp['kode']]
                );
            }
        }

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
