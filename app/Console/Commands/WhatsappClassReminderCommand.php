<?php

namespace App\Console\Commands;

use App\Models\Jadwal;
use App\Models\WhatsappReminderLog;
use App\Services\WhatsApp\WhatsAppNotifier;
use Illuminate\Console\Command;

class WhatsappClassReminderCommand extends Command
{
    protected $signature = 'whatsapp:class-reminder';

    protected $description = 'Kirim WA reminder kelas/mengajar H-1 berdasarkan hari jadwal besok.';

    /**
     * @var array<string, string>
     */
    private const EN_TO_ID = [
        'monday' => 'senin',
        'tuesday' => 'selasa',
        'wednesday' => 'rabu',
        'thursday' => 'kamis',
        'friday' => 'jumat',
        'saturday' => 'sabtu',
        'sunday' => 'minggu',
    ];

    public function handle(WhatsAppNotifier $notifier): int
    {
        if (! $notifier->isEnabled()) {
            $this->info('WhatsApp nonaktif (pengaturan / .env).');

            return self::SUCCESS;
        }

        $en = strtolower(now()->addDay()->format('l'));
        $hariBesok = self::EN_TO_ID[$en] ?? null;
        if ($hariBesok === null) {
            return self::SUCCESS;
        }

        $jadwals = Jadwal::query()
            ->where('hari', $hariBesok)
            ->with(['tutor', 'mataPelajaran', 'cabang', 'siswas'])
            ->get();

        $queued = 0;
        foreach ($jadwals as $jadwal) {
            $dedupeKey = 'class_h1:'.$jadwal->id.':'.now()->format('Y-m-d');
            $log = WhatsappReminderLog::query()->firstOrCreate(
                ['dedupe_key' => $dedupeKey],
                ['type' => 'class_h1']
            );

            if (! $log->wasRecentlyCreated) {
                continue;
            }

            $notifier->notifyTutorClassReminder($jadwal);
            $queued++;
            foreach ($jadwal->siswas as $siswa) {
                $notifier->notifySiswaClassReminder($siswa, $jadwal);
                $queued++;
            }
        }

        $this->info("Reminder kelas H-1 ({$hariBesok}): {$jadwals->count()} jadwal, ~{$queued} pesan diantrekan.");

        return self::SUCCESS;
    }
}
