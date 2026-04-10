<?php

namespace App\Jobs;

use App\Services\WhatsApp\WhatsAppNotifier;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [30, 120, 300];

    public function __construct(
        public string $normalizedTarget,
        public string $message,
    ) {}

    public function handle(WhatsAppService $wa, WhatsAppNotifier $notifier): void
    {
        if (! $notifier->isEnabled()) {
            return;
        }

        $wa->send($this->normalizedTarget, $this->message);
    }
}
