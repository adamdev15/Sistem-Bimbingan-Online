<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'tagline' => 'Bimbel Terpercaya di Tegal untuk Masa Depan Anak Lebih Cerah',
            'hero_title' => 'Bimbel Terbaik di Tegal untuk Meningkatkan Prestasi Anak',
            'hero_desc' => 'Bantu anak memahami pelajaran dengan metode belajar modern, tutor berpengalaman, dan suasana belajar yang nyaman.',
            'about_us' => 'Kami adalah lembaga bimbingan belajar terpercaya yang berfokus pada pendekatan personal, evaluasi berkala, dan penanaman konsep pemahaman materi secara menyeluruh. Telah memberikan ratusan siswa nilai memuaskan.',
            'registration_terms' => '<ol><li>Mengisi form pendaftaran siswa secara lengkap.</li><li>Mengisi data orang tua atau wali siswa untuk keperluan komunikasi laporan.</li><li>Membayar biaya pendaftaran administrasi (jika ada).</li><li>Lakukan pengecekan akun secara berkala untuk info jadwal kelas.</li></ol>',
            'footer_address' => 'Graha Pendidikan, Jl. Pahlawan Karya No. 47, Tegal, Jawa Tengah',
            'footer_phone1' => '6281233640003',
            'footer_phone2' => '6282210880003',
            'footer_email' => 'halo@ebimbeltegal.co.id',
            'footer_web' => 'https://ebimbeltegal.co.id',
            'whatsapp_number' => '6281200000000',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key], 
                ['value' => $value, 'name' => ucwords(str_replace('_', ' ', $key))]
            );
        }
    }
}
