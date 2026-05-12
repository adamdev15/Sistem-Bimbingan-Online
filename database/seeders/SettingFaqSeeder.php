<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingFaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Apa itu Bimbel Jarimatrik?',
                'answer' => 'Bimbel Jarimatrik adalah bimbingan belajar yang berfokus pada metode berhitung cepat menggunakan jari tangan secara profesional dan menyenangkan.'
            ],
            [
                'question' => 'Bagaimana cara mendaftar sebagai siswa?',
                'answer' => 'Anda dapat mendaftar secara online melalui tombol Daftar di website ini atau datang langsung ke cabang terdekat kami.'
            ],
            [
                'question' => 'Apakah tersedia kelas online?',
                'answer' => 'Ya, kami menyediakan sistem bimbingan online yang terintegrasi untuk memudahkan siswa belajar dari mana saja.'
            ],
            [
                'question' => 'Berapa biaya pendaftarannya?',
                'answer' => 'Biaya pendaftaran bervariasi tergantung pada materi les yang dipilih. Silakan hubungi nomor WhatsApp CS kami untuk detail biaya terbaru.'
            ],
        ];

        Setting::updateOrCreate(
            ['setting_key' => 'landing_faq'],
            [
                'value' => json_encode($faqs),
                'name' => 'Landing FAQ',
                'type' => 'json'
            ]
        );
    }
}
