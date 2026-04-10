<?php

namespace App\Services\WhatsApp;

use App\Services\Settings\SettingStore;

class WhatsAppTemplateRenderer
{
    /**
     * @var array<string, string>
     */
    private const DEFAULTS = [
        'wa.template.siswa.invoice_created' => "Halo :nama,\n\nAda tagihan baru :biaya sebesar Rp :nominal.\nJatuh tempo: :due_date.\nNo. ref: :inv",
        'wa.template.siswa.payment_due_tomorrow' => "Halo :nama,\n\nTagihan :biaya (Rp :nominal) jatuh tempo besok (:due_date).\nNo. ref: :inv",
        'wa.template.siswa.payment_success' => "Halo :nama,\n\nPembayaran :biaya sebesar Rp :nominal berhasil.\nNo. ref: :inv",
        'wa.template.siswa.class_schedule' => "Halo :nama,\n\nJadwal kelas :mapel — :hari :jam (:cabang).",
        'wa.template.siswa.class_reminder' => "Halo :nama,\n\nBesok kelas :mapel — :hari :jam (:cabang).",
        'wa.template.tutor.class_schedule' => "Halo :nama,\n\nJadwal mengajar :mapel — :hari :jam (:cabang).",
        'wa.template.tutor.class_reminder' => "Halo :nama,\n\nBesok mengajar :mapel — :hari :jam (:cabang).",
        'wa.template.tutor.salary_paid' => "Halo :nama,\n\nGaji :periode Rp :nominal (status :status).",
        'wa.template.admin.payment_received' => 'Pembayaran masuk — :nama_siswa | :biaya Rp :nominal | :inv | :cabang',
    ];

    public function __construct(
        private readonly SettingStore $settings,
    ) {}

    /**
     * @param  array<string, string|int|float|null>  $replacements  keys without leading ':'
     */
    public function render(string $templateKey, array $replacements): string
    {
        $template = $this->settings->get($templateKey);
        if ($template === null || trim($template) === '') {
            $template = self::DEFAULTS[$templateKey] ?? '';
        }

        $map = [];
        foreach ($replacements as $key => $value) {
            $map[':'.ltrim((string) $key, ':')] = $value === null ? '' : (string) $value;
        }

        return strtr($template, $map);
    }
}
