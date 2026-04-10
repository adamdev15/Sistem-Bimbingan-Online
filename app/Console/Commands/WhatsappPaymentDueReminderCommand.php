<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\WhatsappReminderLog;
use App\Services\WhatsApp\WhatsAppNotifier;
use Illuminate\Console\Command;

class WhatsappPaymentDueReminderCommand extends Command
{
    protected $signature = 'whatsapp:payment-due-reminder';

    protected $description = 'Kirim WA pengingat tagihan jatuh tempo besok (H-1), dengan deduplikasi harian.';

    public function handle(WhatsAppNotifier $notifier): int
    {
        if (! $notifier->isEnabled()) {
            $this->info('WhatsApp nonaktif (pengaturan / .env).');

            return self::SUCCESS;
        }

        $tomorrow = now()->addDay()->toDateString();

        $payments = Payment::query()
            ->belum()
            ->whereDate('due_date', $tomorrow)
            ->with(['siswa', 'fee'])
            ->get();

        $sent = 0;
        foreach ($payments as $payment) {
            $dedupeKey = 'payment_due_h1:'.$payment->id.':'.now()->format('Y-m-d');
            $log = WhatsappReminderLog::query()->firstOrCreate(
                ['dedupe_key' => $dedupeKey],
                ['type' => 'payment_due_h1']
            );

            if (! $log->wasRecentlyCreated) {
                continue;
            }

            $notifier->notifySiswaPaymentDueTomorrow($payment);
            $sent++;
        }

        $this->info("Antrean WA tagihan H-1: {$sent} dari {$payments->count()} tagihan.");

        return self::SUCCESS;
    }
}
