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
    protected $signature = 'sistem:generate-spp';

    protected $description = 'Generate SPP invoices H-2 before due date based on last successful payment date';

    public function __construct(
        private readonly WhatsAppNotifier $whatsapp
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $targetDate = now()->addDays(2);
        $targetDay = $targetDate->day;
        $targetMonthYear = $targetDate->format('Y-m');

        $this->info("Checking students with due day: $targetDay for period: $targetMonthYear");

        $siswas = Siswa::where('status', 'aktif')
            ->whereNotNull('materi_les_id')
            ->whereNotNull('created_at')
            ->get();

        foreach ($siswas as $siswa) {

            $materi = $siswa->materiLes;
            if (!$materi) {
                continue;
            }

            /**
             * =====================================================
             * ACUAN JATUH TEMPO
             * - Jika pernah bayar -> tanggal_bayar terakhir
             * - Jika belum pernah -> tanggal daftar siswa
             * =====================================================
             */
            $lastPayment = Payment::where('student_id', $siswa->id)
                ->where('status', 'lunas')
                ->orderBy('tanggal_bayar', 'desc')
                ->first();

            $anchorDate = $lastPayment && $lastPayment->tanggal_bayar
                ? Carbon::parse($lastPayment->tanggal_bayar)
                : $siswa->created_at;

            $anchorDay = $anchorDate->day;

            $lastDayOfTargetMonth = $targetDate->copy()->endOfMonth()->day;

            if ($anchorDay > $lastDayOfTargetMonth) {
                $isTarget = ($targetDay === $lastDayOfTargetMonth);
            } else {
                $isTarget = ($anchorDay === $targetDay);
            }

            if (!$isTarget) {
                continue;
            }

            /**
             * =====================================================
             * HARGA SPP
             * =====================================================
             */
            $priceRecord = \App\Models\BranchMateriPrice::where('cabang_id', $siswa->cabang_id)
                ->where('materi_les_id', $materi->id)
                ->first();

            $nominal = $priceRecord ? $priceRecord->biaya_spp : 0;

            if ($nominal <= 0) {
                $this->line("Skipping Siswa {$siswa->nama}: No active SPP price found.");
                continue;
            }

            /**
             * =====================================================
             * PENENTUAN JENIS BIAYA
             * =====================================================
             */
            $materiName = strtolower($materi->nama_materi);

            $feeSppName = 'SPP - Les Mapel';

            if (str_contains($materiName, 'jarimatika')) {
                $feeSppName = 'SPP - Jarimatika';
            } elseif (str_contains($materiName, 'calistung')) {
                $feeSppName = 'SPP - Les Calistung';
            } elseif (str_contains($materiName, 'matematika')) {
                $feeSppName = 'SPP - Les Matematika Intensif';
            } elseif (str_contains($materiName, 'iec')) {
                $feeSppName = 'SPP - Les IEC';
            } elseif (str_contains($materiName, 'coding')) {
                $feeSppName = 'SPP - Les Coding';
            }

            $fee = Fee::where('nama_biaya', $feeSppName)->first();

            if (!$fee) {
                $this->error("Skipping Siswa {$siswa->nama}: Fee '{$feeSppName}' not found.");
                continue;
            }

            /**
             * =====================================================
             * CEK DUPLIKAT TAGIHAN
             * =====================================================
             */
            $exists = Payment::where('student_id', $siswa->id)
                ->where('invoice_period', $targetMonthYear)
                ->where('biaya_id', $fee->id)
                ->exists();

            if ($exists) {
                $this->line("Skipping Siswa {$siswa->nama}: Invoice already exists for {$targetMonthYear}");
                continue;
            }

            /**
             * =====================================================
             * GENERATE TAGIHAN
             * =====================================================
             */
            $payment = Payment::create([
                'order_id' => 'SPP-' . time() . rand(1000, 9999),
                'student_id' => $siswa->id,
                'biaya_id' => $fee->id,
                'invoice_period' => $targetMonthYear,
                'nominal' => $nominal,

                // BELUM DIBAYAR
                'tanggal_bayar' => null,

                'due_date' => $targetDate->format('Y-m-d'),
                'tanggal_jatuh_tempo' => $targetDate->format('Y-m-d'),

                'status' => 'belum',

                'catatan' => "Tagihan SPP otomatis ({$feeSppName}) untuk periode {$targetMonthYear} (H-2 Jatuh Tempo).",
            ]);

            /**
             * =====================================================
             * WHATSAPP NOTIFIKASI
             * =====================================================
             */
            try {
                $this->whatsapp->notifySiswaInvoiceCreated($payment);

                $this->info(
                    "Generated SPP for {$siswa->nama} | Acuan: {$anchorDate->format('d-m-Y')}"
                );
            } catch (\Exception $e) {
                Log::error(
                    "Failed to send WhatsApp for SPP Siswa {$siswa->id}: " .
                    $e->getMessage()
                );

                $this->error(
                    "Generated SPP for {$siswa->nama} but WhatsApp failed."
                );
            }
        }

        $this->info('Generation process completed.');
    }
}