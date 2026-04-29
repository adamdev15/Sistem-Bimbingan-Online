<?php

namespace App\Services\Notifications;

use App\Models\Jadwal;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\WhatsApp\WhatsAppNotifier;

class InAppBellNotifier
{
    public function __construct(
        private readonly WhatsAppNotifier $whatsapp,
    ) {}

    public function paymentInvoiceCreated(Payment $payment, ?User $sender): void
    {
        $payment->loadMissing(['siswa.user', 'fee']);
        $user = $payment->siswa?->user;
        if ($user === null) {
            return;
        }

        $feeLabel = $payment->fee?->nama_biaya ?? 'Biaya';
        $amount = number_format((float) $payment->nominal, 0, ',', '.');
        $due = $payment->due_date?->translatedFormat('d M Y') ?? '—';

        UserNotification::query()->create([
            'user_id' => $user->id,
            'sender_user_id' => $sender?->id,
            'type' => 'payment.invoice',
            'title' => 'Tagihan baru: '.$feeLabel,
            'body' => 'Nominal Rp '.$amount.'. Jatuh tempo: '.$due.'.',
            'subject_type' => Payment::class,
            'subject_id' => $payment->id,
            'action_route' => 'pembayaran.index',
            'action_params' => [],
        ]);

        $this->whatsapp->notifySiswaInvoiceCreated($payment);
    }

    /**
     * @param  list<int>  $paymentIds
     */
    public function paymentDueReminderBulk(array $paymentIds, User $sender): int
    {
        $count = 0;
        $payments = Payment::query()
            ->belum()
            ->whereIn('id', $paymentIds)
            ->with(['siswa.user', 'fee'])
            ->get();

        foreach ($payments as $payment) {
            $user = $payment->siswa?->user;
            if ($user === null) {
                continue;
            }

            $feeLabel = $payment->fee?->nama_biaya ?? 'Biaya';
            $amount = number_format((float) $payment->nominal, 0, ',', '.');
            $due = $payment->due_date?->translatedFormat('d M Y') ?? '—';

            UserNotification::query()->create([
                'user_id' => $user->id,
                'sender_user_id' => $sender->id,
                'type' => 'payment.due_reminder',
                'title' => 'Pengingat jatuh tempo: '.$feeLabel,
                'body' => 'Rp '.$amount.' — jatuh tempo '.$due.'. Silakan selesaikan pembayaran.',
                'subject_type' => Payment::class,
                'subject_id' => $payment->id,
                'action_route' => 'pembayaran.index',
                'action_params' => [],
            ]);
            $count++;
        }

        return $count;
    }

    public function paymentSettled(Payment $payment, ?User $actor = null): void
    {
        $payment->loadMissing(['siswa.user', 'fee']);
        $user = $payment->siswa?->user;
        if ($user === null) {
            return;
        }

        $feeLabel = $payment->fee?->nama_biaya ?? 'Biaya';
        $amount = number_format((float) $payment->nominal, 0, ',', '.');

        UserNotification::query()->create([
            'user_id' => $user->id,
            'sender_user_id' => $actor?->id,
            'type' => 'payment.paid',
            'title' => 'Pembayaran lunas',
            'body' => $feeLabel.' — Rp '.$amount.' telah diterima. Terima kasih.',
            'subject_type' => Payment::class,
            'subject_id' => $payment->id,
            'action_route' => 'pembayaran.index',
            'action_params' => [],
        ]);

        $this->whatsapp->notifySiswaPaymentSuccess($payment);
    }

    public function jadwalCreated(Jadwal $jadwal): void
    {
        $jadwal->loadMissing(['tutor.user', 'mataPelajaran', 'cabang']);
        $tutorUser = $jadwal->tutor?->user;
        if ($tutorUser === null) {
            return;
        }

        $mapel = $jadwal->mataPelajaran?->nama ?? 'Mapel';
        $cabang = $jadwal->cabang?->nama_cabang ?? '';

        UserNotification::query()->create([
            'user_id' => $tutorUser->id,
            'sender_user_id' => auth()->id(),
            'type' => 'jadwal.created',
            'title' => 'Jadwal mengajar baru',
            'body' => $mapel.' — '.ucfirst((string) $jadwal->hari).' '.$jadwal->jam_mulai.'–'.$jadwal->jam_selesai.($cabang !== '' ? ' ('.$cabang.')' : ''),
            'subject_type' => Jadwal::class,
            'subject_id' => $jadwal->id,
            'action_route' => 'jadwal.index',
            'action_params' => [],
        ]);

        $this->whatsapp->notifyTutorJadwalCreated($jadwal);
    }
}
