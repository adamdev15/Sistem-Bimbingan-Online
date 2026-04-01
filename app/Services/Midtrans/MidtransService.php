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
        $email = $siswa->email ?: $user?->email ?: 'siswa@ebimbel.local';
        $name = $siswa->nama ?: ($user?->name ?? 'Siswa');

        $itemName = $payment->fee?->nama_biaya ?? 'Pembayaran';

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $gross,
            ],
            'customer_details' => [
                'first_name' => mb_substr($name, 0, 50),
                'email' => $email,
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

        $finish = url(config('midtrans.finish_redirect_path'));
        $params['callbacks'] = [
            'finish' => $finish,
        ];

        try {
            $token = Snap::getSnapToken($params);
        } catch (\Throwable $e) {
            Log::error('Midtrans Snap gagal', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);

            throw new RuntimeException('Gagal membuat sesi pembayaran Midtrans. Coba lagi nanti.');
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

        $transactionStatus = $s['transaction_status'] ?? null;
        $fraud = $s['fraud_status'] ?? null;
        $paymentType = $s['payment_type'] ?? null;
        $transactionId = $s['transaction_id'] ?? null;

        $payload = array_merge($payment->midtrans_payload ?? [], [
            'last_sync' => $s,
        ]);

        $payment->forceFill([
            'midtrans_transaction_id' => is_string($transactionId) ? $transactionId : $payment->midtrans_transaction_id,
            'midtrans_payment_type' => is_string($paymentType) ? $paymentType : $payment->midtrans_payment_type,
            'midtrans_transaction_status' => is_string($transactionStatus) ? $transactionStatus : $payment->midtrans_transaction_status,
            'midtrans_payload' => $payload,
        ]);

        $shouldLunas = false;

        if ($transactionStatus === 'settlement') {
            $shouldLunas = true;
        } elseif ($transactionStatus === 'capture') {
            $shouldLunas = $fraud !== 'challenge';
        }

        if ($shouldLunas && ! $payment->isLunas()) {
            $payment->status = 'lunas';
            $payment->paid_at = now();
        }

        if (in_array($transactionStatus, ['expire', 'cancel', 'deny'], true)) {
            $payment->midtrans_transaction_status = (string) $transactionStatus;
        }

        $payment->save();
    }
}
