<?php

namespace App\Services\WhatsApp;

use App\Jobs\SendWhatsAppJob;
use App\Models\Jadwal;
use App\Models\Payment;
use App\Models\Salary;
use App\Models\Siswa;
use App\Services\Settings\SettingStore;

class WhatsAppNotifier
{
    public function __construct(
        private readonly SettingStore $settings,
        private readonly WhatsAppService $waService,
        private readonly WhatsAppTemplateRenderer $templates,
    ) {}

    public function isEnabled(): bool
    {
        $db = $this->settings->get('whatsapp.enabled', null);
        if ($db === null) {
            return (bool) config('services.whatsapp.enabled', false);
        }

        return in_array(strtolower(trim((string) $db)), ['1', 'true', 'yes', 'on'], true);
    }

    public function queueToPhone(?string $rawPhone, string $message): void
    {
        if (! $this->isEnabled() || trim($message) === '') {
            return;
        }

        $target = $this->waService->normalizePhone($rawPhone);
        if ($target === null) {
            return;
        }

        SendWhatsAppJob::dispatch($target, $message);
    }

    public function notifySiswaInvoiceCreated(Payment $payment): void
    {
        $payment->loadMissing(['siswa', 'fee']);
        $siswa = $payment->siswa;
        if ($siswa === null) {
            return;
        }

        $msg = $this->templates->render('wa.template.siswa.invoice_created', $this->paymentPlaceholders($payment, $siswa));
        $this->queueToPhone($siswa->phoneForNotification(), $msg);
    }

    public function notifySiswaPaymentSuccess(Payment $payment): void
    {
        $payment->loadMissing(['siswa', 'fee']);
        $siswa = $payment->siswa;
        if ($siswa === null) {
            return;
        }

        $msg = $this->templates->render('wa.template.siswa.payment_success', $this->paymentPlaceholders($payment, $siswa));
        $this->queueToPhone($siswa->phoneForNotification(), $msg);
    }

    public function notifySiswaPaymentDueTomorrow(Payment $payment): void
    {
        $payment->loadMissing(['siswa', 'fee']);
        $siswa = $payment->siswa;
        if ($siswa === null) {
            return;
        }

        $msg = $this->templates->render('wa.template.siswa.payment_due_tomorrow', $this->paymentPlaceholders($payment, $siswa));
        $this->queueToPhone($siswa->phoneForNotification(), $msg);
    }

    public function notifyTutorSalaryPaid(Salary $salary): void
    {
        $salary->loadMissing('tutor');
        $tutor = $salary->tutor;
        if ($tutor === null) {
            return;
        }

        $msg = $this->templates->render('wa.template.tutor.salary_paid', [
            'nama' => $tutor->nama,
            'periode' => $salary->periode,
            'nominal' => number_format((float) $salary->total_gaji, 0, ',', '.'),
            'status' => $salary->status,
        ]);
        $this->queueToPhone($tutor->no_hp, $msg);
    }

    public function notifyTutorJadwalCreated(Jadwal $jadwal): void
    {
        $jadwal->loadMissing(['tutor', 'mataPelajaran', 'cabang']);
        $tutor = $jadwal->tutor;
        if ($tutor === null) {
            return;
        }

        $msg = $this->templates->render('wa.template.tutor.class_schedule', [
            'nama' => $tutor->nama,
            'mapel' => $jadwal->mataPelajaran->nama_materi ?? 'Materi Les',
            'hari' => ucfirst((string) $jadwal->hari),
            'jam' => $jadwal->jam_mulai . ' - ' . $jadwal->jam_selesai,
            'cabang' => $jadwal->cabang->nama_cabang ?? 'Cabang',
        ]);
        $this->queueToPhone($tutor->no_hp, $msg);
    }

    public function notifyAdminPaymentReceived(Payment $payment): void
    {
        // Removed per user request - Admin notification handled via In-App Bell only.
    }

    /**
     * @return array<string, string>
     */
    private function paymentPlaceholders(Payment $payment, ?Siswa $siswa): array
    {
        $feeLabel = $payment->fee?->nama_biaya ?? 'Biaya';

        return [
            'nama' => $siswa?->nama ?? 'Siswa',
            'biaya' => $feeLabel,
            'nominal' => number_format((float) $payment->nominal, 0, ',', '.'),
            'due_date' => $payment->due_date?->translatedFormat('d M Y') ?? '—',
            'inv' => $payment->order_id ?? ('INV-'.str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT)),
        ];
    }
}
