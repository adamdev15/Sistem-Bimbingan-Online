<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Payment;
use App\Models\Fee;
use App\Services\WhatsApp\WhatsAppNotifier;
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
    protected $description = 'Generate SPP invoices H-2 before due date based on student registration date';

    public function __construct(
        private readonly WhatsAppNotifier $whatsapp
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetDate = now()->addDays(2);
        $targetDay = $targetDate->day;
        $targetMonthYear = $targetDate->format('Y-m');

        $this->info("Checking students with due day: $targetDay for period: $targetMonthYear");

        // Find active students whose created_at day matches the target day
        $siswas = Siswa::where('status', 'aktif')
            ->whereNotNull('materi_les_id')
            ->whereNotNull('created_at')
            ->get()
            ->filter(function ($siswa) use ($targetDay) {
                if (!$siswa->created_at) return false;
                
                $anchorDay = $siswa->created_at->day;
                
                // Handle cases where anchor day is 31 and target month has fewer days
                $lastDayOfTargetMonth = now()->addDays(2)->endOfMonth()->day;
                if ($anchorDay > $lastDayOfTargetMonth) {
                    return $targetDay === $lastDayOfTargetMonth;
                }
                
                return $anchorDay === $targetDay;
            });

        foreach ($siswas as $siswa) {
            $materi = $siswa->materiLes;
            if (!$materi || $materi->biaya_spp <= 0) {
                continue;
            }

            // Check if invoice already exists for this period
            $exists = Payment::where('student_id', $siswa->id)
                ->where('invoice_period', $targetMonthYear)
                ->whereHas('fee', function ($query) {
                    $query->where('tipe', 'bulanan');
                })
                ->exists();

            if ($exists) {
                $this->line("Skipping Siswa {$siswa->nama}: Invoice already exists for $targetMonthYear");
                continue;
            }

            // Create the payment
            $payment = Payment::create([
                'order_id' => 'SPP-' . time() . rand(1000, 9999),
                'student_id' => $siswa->id,
                'biaya_id' => 9, // SPP Bulanan Bimbel Jarimatrik
                'invoice_period' => $targetMonthYear,
                'nominal' => $materi->biaya_spp,
                'tanggal_bayar' => now()->format('Y-m-d'),
                'due_date' => $targetDate->format('Y-m-d'),
                'tanggal_jatuh_tempo' => $targetDate->format('Y-m-d'),
                'status' => 'belum',
                'catatan' => "Tagihan SPP otomatis untuk periode $targetMonthYear (H-2 Jatuh Tempo).",
            ]);

            // Notify via WhatsApp
            try {
                $this->whatsapp->notifySiswaInvoiceCreated($payment);
                $this->info("Generated and notified SPP for Siswa {$siswa->nama}");
            } catch (\Exception $e) {
                Log::error("Failed to send WhatsApp for SPP Siswa {$siswa->id}: " . $e->getMessage());
                $this->error("Generated SPP for Siswa {$siswa->nama} but WhatsApp failed.");
            }
        }

        $this->info("Generation process completed.");
    }
}
