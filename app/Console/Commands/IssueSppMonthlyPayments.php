<?php

namespace App\Console\Commands;

use App\Models\Fee;
use App\Models\Payment;
use App\Models\Siswa;
use App\Services\Notifications\InAppBellNotifier;
use Carbon\Carbon;
use Illuminate\Console\Command;

class IssueSppMonthlyPayments extends Command
{
    protected $signature = 'payments:issue-spp-monthly {--period= : Format YYYY-MM (default: bulan berjalan)}';

    protected $description = 'Membuat tagihan SPP bulanan (fee tipe bulanan) untuk semua siswa aktif yang belum punya invoice periode tersebut';

    public function handle(InAppBellNotifier $bell): int
    {
        $period = $this->option('period') ?: now()->format('Y-m');
        if (! preg_match('/^\d{4}-\d{2}$/', $period)) {
            $this->error('Format --period harus YYYY-MM');

            return self::FAILURE;
        }

        $fees = Fee::query()->where('tipe', 'bulanan')->get();
        if ($fees->isEmpty()) {
            $this->warn('Tidak ada master biaya bertipe bulanan.');

            return self::SUCCESS;
        }

        $siswas = Siswa::query()->where('status', 'aktif')->get();
        $due = Carbon::createFromFormat('Y-m', $period)->endOfMonth();

        $created = 0;
        foreach ($siswas as $siswa) {
            foreach ($fees as $fee) {
                $exists = Payment::query()
                    ->where('student_id', $siswa->id)
                    ->where('biaya_id', $fee->id)
                    ->where('invoice_period', $period)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $payment = Payment::query()->create([
                    'student_id' => $siswa->id,
                    'biaya_id' => $fee->id,
                    'invoice_period' => $period,
                    'nominal' => $fee->nominal,
                    'tanggal_bayar' => now()->toDateString(),
                    'due_date' => $due->toDateString(),
                    'status' => 'belum',
                    'created_by' => null,
                ]);

                $bell->paymentInvoiceCreated($payment, null);
                $created++;
            }
        }

        $this->info("Tagihan SPP dibuat: {$created} (periode {$period}).");

        return self::SUCCESS;
    }
}
