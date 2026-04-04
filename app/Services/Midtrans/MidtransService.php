<?php

namespace App\Services\Midtrans;

use App\Models\Payment;
use App\Models\Siswa;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use RuntimeException;

class MidtransService
{
    public function applyConfig(): void
    {
        Config::$serverKey = (string) config('midtrans.server_key');
        Config::$clientKey = (string) config('midtrans.client_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized = (bool) config('midtrans.is_sanitized');
        Config::$is3ds = (bool) config('midtrans.is_3ds');

        $notify = config('midtrans.notification_url');
        if (is_string($notify) && $notify !== '') {
            Config::$overrideNotifUrl = $notify;
        }
    }

    /**
     * Verifikasi signature_key dari body notifikasi HTTP Midtrans.
     *
     * @param  array<string, mixed>  $payload
     */
    public function verifyNotificationSignature(array $payload): bool
    {
        $signatureKey = $payload['signature_key'] ?? null;
        $orderId = $payload['order_id'] ?? null;
        $statusCode = isset($payload['status_code']) ? (string) $payload['status_code'] : null;
        $grossAmount = isset($payload['gross_amount']) ? (string) $payload['gross_amount'] : null;
        $serverKey = (string) config('midtrans.server_key');

        if (! is_string($signatureKey) || ! is_string($orderId) || $statusCode === null || $grossAmount === null || $serverKey === '') {
            return false;
        }

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expected, $signatureKey);
    }

    /**
     * Ambil status transaksi dari API Midtrans (order_id atau transaction_id).
     *
     * @return object|array<string, mixed>
     */
    public function fetchTransactionStatus(string $id): object|array
    {
        $this->applyConfig();

        return Transaction::status($id);
    }

    /**
     * Buat Snap token untuk pembayaran tagihan siswa.
     */
    public function createSnapToken(Payment $payment): string
    {
        $this->applyConfig();

        $serverKey = (string) config('midtrans.server_key');
        if ($serverKey === '' || str_contains($serverKey, 'xxxxxxxx')) {
            throw new RuntimeException('Midtrans server key belum dikonfigurasi di .env');
        }

        $payment->loadMissing(['siswa.user', 'fee']);

        /** @var Siswa|null $siswa */
        $siswa = $payment->siswa;
        if ($siswa === null) {
            throw new RuntimeException('Data siswa tidak ditemukan.');
        }

        $orderId = 'EBL-'.$payment->id.'-'.bin2hex(random_bytes(4));
        $payment->forceFill([
            'order_id' => $orderId,
            'midtrans_transaction_status' => 'pending',
        ])->save();

        $gross = $payment->grossAmountIdr();
        if ($gross < 1) {
            throw new RuntimeException('Nominal pembayaran tidak valid.');
        }

        $user = $siswa->user;
        $email = $siswa->email ?: $user?->email ?: 'siswa@example.com';
        $name = trim((string) ($siswa->nama ?: ($user?->name ?? 'Siswa')));
        if ($name === '') {
            $name = 'Siswa';
        }

        $phone = preg_replace('/\D+/', '', (string) ($siswa->no_hp ?? ''));
        if (strlen($phone) < 9) {
            $phone = '081234567890';
        }

        $itemName = $payment->fee?->nama_biaya ?? 'Pembayaran';

        // Payload mengikuti contoh resmi Snap; hindari field non-standar (mis. callbacks) yang bisa ditolak API.
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $gross,
            ],
            'customer_details' => [
                'first_name' => mb_substr($name, 0, 50),
                'last_name' => '-',
                'email' => $email,
                'phone' => $phone,
            ],
            'item_details' => [
                [
                    'id' => (string) $payment->biaya_id,
                    'price' => $gross,
                    'quantity' => 1,
                    'name' => mb_substr($itemName, 0, 50),
                ],
            ],
        ];

        try {
            $token = Snap::getSnapToken($params);
        } catch (\Throwable $e) {
            Log::error('Midtrans Snap gagal', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);

            $hint = config('app.debug') ? ' '.$e->getMessage() : '';

            throw new RuntimeException('Gagal menghubungi Midtrans (periksa Server Key sandbox/production dan koneksi).'.$hint);
        }

        return $token;
    }

    /**
     * Terapkan hasil status transaksi ke model Payment.
     *
     * @param  object|array<string, mixed>  $status
     */
    public function syncPaymentFromMidtransStatus(Payment $payment, object|array $status): void
    {
        $s = is_object($status) ? get_object_vars($status) : $status;

        $rawStatus = $s['transaction_status'] ?? null;
        $transactionStatus = is_string($rawStatus) ? strtolower(trim($rawStatus)) : null;

        $rawFraud = $s['fraud_status'] ?? null;
        $fraud = is_string($rawFraud) ? strtolower(trim($rawFraud)) : null;

        $paymentType = $s['payment_type'] ?? null;
        $transactionId = $s['transaction_id'] ?? null;

        $payload = array_merge($payment->midtrans_payload ?? [], [
            'last_sync' => $s,
        ]);

        $payment->forceFill([
            'midtrans_transaction_id' => is_string($transactionId) ? $transactionId : $payment->midtrans_transaction_id,
            'midtrans_payment_type' => is_string($paymentType) ? $paymentType : $payment->midtrans_payment_type,
            'midtrans_transaction_status' => $transactionStatus ?? $payment->midtrans_transaction_status,
            'midtrans_payload' => $payload,
        ]);

        $shouldLunas = false;

        // VA, QRIS, retail, dll. biasanya settlement; kartu kredit capture (bukan challenge).
        if ($transactionStatus === 'settlement') {
            $shouldLunas = true;
        } elseif ($transactionStatus === 'capture') {
            $shouldLunas = $fraud !== 'challenge';
        }

        if ($shouldLunas && ! $payment->isLunas()) {
            $payment->status = 'lunas';
            $payment->paid_at = now();
        }

        if ($transactionStatus !== null && in_array($transactionStatus, ['expire', 'cancel', 'deny'], true)) {
            $payment->midtrans_transaction_status = $transactionStatus;
        }

        $payment->save();
    }
}
