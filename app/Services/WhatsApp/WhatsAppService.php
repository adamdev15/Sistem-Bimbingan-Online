<?php

namespace App\Services\WhatsApp;

use App\Services\Settings\SettingStore;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function __construct(
        private readonly SettingStore $settings,
    ) {}

    /**
     * Normalize Indonesian phone to digits with 62 prefix (no +).
     */
    public function normalizePhone(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '62')) {
            return strlen($digits) >= 11 ? $digits : null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        } else {
            return null;
        }

        return strlen($digits) >= 11 ? $digits : null;
    }

    public function send(string $normalizedTarget, string $message): void
    {
        // Prioritize Settings table, fallback to .env/config
        $url = config('services.whatsapp.url', 'https://api.fonnte.com/send');
        $token = (string) $this->settings->get('whatsapp.token') ?: config('services.whatsapp.key');

        if (empty($url) || empty($token)) {
            Log::notice('WhatsApp: URL atau token belum dikonfigurasi.');
            return;
        }

        if (empty($normalizedTarget) || empty($message)) {
            return;
        }

        try {
            $response = Http::timeout(25)
                ->withHeaders([
                    'Authorization' => $token,
                ])
                ->asForm()
                ->post($url, [
                    'target' => $normalizedTarget,
                    'message' => $message,
                    // 'delay' => '2', // Optional: Fonnte specific
                    // 'countryCode' => '62', // Optional
                ]);

            if (!$response->successful()) {
                Log::warning('WhatsApp: Gagal mengirim pesan', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'target' => $normalizedTarget,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp: request error', [
                'error' => $e->getMessage(),
                'target' => $normalizedTarget,
            ]);
        }
    }
}
