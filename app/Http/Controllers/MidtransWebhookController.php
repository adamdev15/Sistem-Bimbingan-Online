<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Midtrans\MidtransService;
use App\Services\Notifications\InAppBellNotifier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtrans,
        private readonly InAppBellNotifier $bell,
    ) {}

    public function handle(Request $request): Response
    {
        $raw = $request->getContent();
        $payload = json_decode($raw, true);

        if (! is_array($payload)) {
            return response('Invalid JSON', 400);
        }

        if (! $this->midtrans->verifyNotificationSignature($payload)) {
            Log::warning('Midtrans webhook signature tidak valid', ['order_id' => $payload['order_id'] ?? null]);

            return response('Invalid signature', 403);
        }

        $orderId = $payload['order_id'] ?? null;
        if (! is_string($orderId) || $orderId === '') {
            return response('Missing order_id', 400);
        }

        $payment = Payment::query()->where('order_id', $orderId)->first();
        if ($payment === null) {
            Log::notice('Midtrans webhook: order_id tidak dikenal', ['order_id' => $orderId]);

            return response('OK', 200);
        }

        $grossPayload = isset($payload['gross_amount']) ? (string) $payload['gross_amount'] : '';
        if ($grossPayload !== '') {
            $expected = (float) number_format($payment->grossAmountIdr(), 2, '.', '');
            if (abs((float) $grossPayload - $expected) > 0.01) {
                Log::warning('Midtrans webhook: nominal tidak cocok', [
                    'payment_id' => $payment->id,
                    'expected' => $expected,
                    'got' => $grossPayload,
                ]);

                return response('Amount mismatch', 400);
            }
        }

        try {
            $remote = $this->midtrans->fetchTransactionStatus($orderId);
        } catch (\Throwable $e) {
            Log::error('Midtrans status API gagal', ['order_id' => $orderId, 'error' => $e->getMessage()]);

            return response('Upstream error', 502);
        }

        $beforeLunas = $payment->isLunas();
        $this->midtrans->syncPaymentFromMidtransStatus($payment, $remote);
        $payment->refresh();

        if (! $beforeLunas && $payment->isLunas()) {
            $this->bell->paymentSettled($payment);
        }

        return response('OK', 200);
    }
}
