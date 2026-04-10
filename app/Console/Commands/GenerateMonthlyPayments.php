<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sistem:generate-spp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate automatic SPP monthly payments for active students';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting monthly payment generation...');
        $count = 0;
        
        $siswas = Siswa::with('materiLes.fee')->where('status', 'aktif')->get();
        // Catatan: Siswa yang berstatus 'cuti' tidak akan degenerate tagihannya.
        // Jika mereka kembali 'aktif', tagihan bulan berjalan akan degenerate jika belum ada.
        
        $now = now();
        $currentPeriod = $now->format('Y-m');

        foreach ($siswas as $siswa) {
            if (!$siswa->materi_les_id || !$siswa->materiLes || !$siswa->materiLes->fee_id) {
                continue;
            }

            // Check if already billed for this period
            $existing = Payment::where('student_id', $siswa->id)
                ->where('biaya_id', $siswa->materiLes->fee_id)
                ->where('invoice_period', $currentPeriod)
                ->exists();

            if (!$existing) {
                $dueDate = $now->copy()->addDays(7);
                
                Payment::create([
                    'order_id' => 'SPP-' . time() . rand(1000, 9999),
                    'student_id' => $siswa->id,
                    'biaya_id' => $siswa->materiLes->fee_id,
                    'invoice_period' => $currentPeriod,
                    'nominal' => $siswa->materiLes->fee->nominal ?? 0,
                    'tanggal_bayar' => $now->format('Y-m-d'),
                    'due_date' => $dueDate->format('Y-m-d'),
                    'tanggal_jatuh_tempo' => $dueDate->format('Y-m-d'),
                    'status' => 'belum',
                    'catatan' => 'Tagihan otomatis untuk SPP Bulan ' . $now->translatedFormat('F Y'),
                ]);
                $count++;
            }
        }
        
        $this->info("Successfully generated {$count} new payments.");
        Log::info("sistem:generate-spp ran at {$now->toDateTimeString()}: created {$count} new payments.");
    }
}
