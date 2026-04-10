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
        $url = trim((string) ($this->settings->get('whatsapp.api_url') ?: config('services.whatsapp.url', '')));
        $token = trim((string) ($this->settings->get('whatsapp.token') ?: config('services.whatsapp.key', '')));

        if ($url === '' || $token === '') {
            Log::notice('WhatsApp: URL atau token kosong, pesan tidak dikirim.');

            return;
        }

        if ($normalizedTarget === '' || $message === '') {
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
                ]);
        } catch (\Throwable $e) {
            Log::error('WhatsApp: request gagal', ['error' => $e->getMessage()]);

            return;
        }

        if (! $response->successful()) {
            $body = $response->body();
            Log::warning('WhatsApp: response non-success', [
                'status' => $response->status(),
                'body' => mb_substr($body, 0, 500),
            ]);
        }
    }
}
