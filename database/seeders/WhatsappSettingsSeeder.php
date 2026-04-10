<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class WhatsappSettingsSeeder extends Seeder
{
    /**
     * @return list<array{setting_key: string, name: string, type: string, value: string}>
     */
    public static function definitionRows(): array
    {
        return [
            [
                'setting_key' => 'whatsapp.enabled',
                'name' => 'WhatsApp aktif',
                'type' => 'text',
                'value' => '0',
            ],
            [
                'setting_key' => 'whatsapp.api_url',
                'name' => 'URL API WhatsApp (Fonnte)',
                'type' => 'text',
                'value' => 'https://api.fonnte.com/send',
            ],
            [
                'setting_key' => 'whatsapp.token',
                'name' => 'Token / API key Fonnte',
                'type' => 'longtext',
                'value' => '',
            ],
            [
                'setting_key' => 'wa.admin.super_phones',
                'name' => 'Nomor admin pusat (CSV, 62xxx)',
                'type' => 'text',
                'value' => '',
            ],
            [
                'setting_key' => 'wa.template.siswa.invoice_created',
                'name' => 'Siswa: tagihan dibuat',
                'type' => 'longtext',
                'value' => "Halo :nama,\n\nAda tagihan baru :biaya sebesar Rp :nominal.\nJatuh tempo: :due_date.\nNo. ref: :inv\n\nSilakan cek menu Pembayaran di eBimbel.\nTerima kasih.",
            ],
            [
                'setting_key' => 'wa.template.siswa.payment_due_tomorrow',
                'name' => 'Siswa: pengingat jatuh tempo (H-1)',
                'type' => 'longtext',
                'value' => "Halo :nama,\n\nTagihan :biaya (Rp :nominal) jatuh tempo besok (:due_date).\nNo. ref: :inv\n\nMohon selesaikan pembayaran tepat waktu.\nTerima kasih.",
            ],
            [
                'setting_key' => 'wa.template.siswa.payment_success',
                'name' => 'Siswa: pembayaran berhasil',
                'type' => 'longtext',
                'value' => "Halo :nama,\n\nPembayaran :biaya sebesar Rp :nominal telah berhasil kami terima.\nNo. ref: :inv\n\nTerima kasih.",
            ],
            [
                'setting_key' => 'wa.template.siswa.class_schedule',
                'name' => 'Siswa: jadwal kelas baru',
                'type' => 'longtext',
                'value' => "Halo :nama,\n\nAnda terdaftar di kelas :mapel.\nHari: :hari | Jam: :jam\nCabang: :cabang\n\nCek menu Jadwal di eBimbel.",
            ],
            [
                'setting_key' => 'wa.template.siswa.class_reminder',
                'name' => 'Siswa: reminder kelas (H-1)',
                'type' => 'longtext',
                'value' => "Halo :nama,\n\nBesok ada kelas :mapel.\nHari: :hari | Jam: :jam\nCabang: :cabang\n\nSampai jumpa di kelas.",
            ],
            [
                'setting_key' => 'wa.template.tutor.class_schedule',
                'name' => 'Tutor: jadwal mengajar baru',
                'type' => 'longtext',
                'value' => "Halo :nama,\n\nJadwal mengajar baru: :mapel\nHari: :hari | Jam: :jam\nCabang: :cabang\n\nCek menu Jadwal di eBimbel.",
            ],
            [
                'setting_key' => 'wa.template.tutor.class_reminder',
                'name' => 'Tutor: reminder mengajar (H-1)',
                'type' => 'longtext',
                'value' => "Halo :nama,\n\nBesok ada jadwal mengajar :mapel.\nHari: :hari | Jam: :jam\nCabang: :cabang",
            ],
            [
                'setting_key' => 'wa.template.tutor.salary_paid',
                'name' => 'Tutor: gaji dibayarkan',
                'type' => 'longtext',
                'value' => "Halo :nama,\n\nGaji periode :periode sebesar Rp :nominal dengan status :status telah diperbarui.\n\nTerima kasih atas dedikasi Anda.",
            ],
            [
                'setting_key' => 'wa.template.admin.payment_received',
                'name' => 'Admin: pembayaran masuk',
                'type' => 'longtext',
                'value' => "eBimbel — Pembayaran masuk\n\nSiswa: :nama_siswa\n:biaya — Rp :nominal\nNo. ref: :inv\nCabang: :cabang",
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::definitionRows() as $row) {
            Setting::query()->updateOrCreate(
                ['setting_key' => $row['setting_key']],
                [
                    'name' => $row['name'],
                    'type' => $row['type'],
                    'value' => $row['value'],
                ]
            );
        }
    }
}
