<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Settings\SettingStore;
use Database\Seeders\WhatsappSettingsSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsappSettingsController extends Controller
{
    /**
     * @return list<string>
     */
    private static function allowedKeys(): array
    {
        return array_column(WhatsappSettingsSeeder::definitionRows(), 'setting_key');
    }

    public function __construct(
        private readonly SettingStore $settingStore,
    ) {}

    public function edit(Request $request): View
    {
        abort_unless($request->user()?->hasRole('super_admin'), 403);

        if (! Setting::query()->where('setting_key', 'whatsapp.enabled')->exists()) {
            (new WhatsappSettingsSeeder)->run();
        }

        // Only show enabled toggle, token and templates
        $settings = Setting::query()
            ->where(function ($q) {
                $q->whereIn('setting_key', ['whatsapp.enabled', 'whatsapp.token'])
                    ->orWhere('setting_key', 'LIKE', 'wa.template.%');
            })
            ->orderByRaw("FIELD(setting_key, 'whatsapp.enabled', 'whatsapp.token') DESC, setting_key ASC")
            ->get();

        $placeholdersHelp = [
            'wa.template.siswa.invoice_created' => ':nama, :biaya, :nominal, :due_date, :inv',
            'wa.template.siswa.payment_due_tomorrow' => ':nama, :biaya, :nominal, :due_date, :inv',
            'wa.template.siswa.payment_success' => ':nama, :biaya, :nominal, :due_date, :inv',
            'wa.template.tutor.salary_paid' => ':nama, :periode, :nominal, :status',
        ];

        return view('modules.whatsapp-settings.edit', [
            'settings' => $settings,
            'placeholdersHelp' => $placeholdersHelp,
        ]);
    }

    public function test(Request $request, \App\Services\WhatsApp\WhatsAppService $waService): RedirectResponse
    {
        $request->validate([
            'target' => ['required', 'string'],
        ]);

        $target = $waService->normalizePhone($request->target);
        if (!$target) {
            return back()->withErrors(['test_target' => 'Nomor HP tidak valid. Gunakan format 628xxx atau 08xxx.']);
        }

        $waService->send($target, 'Bimbel Jarimatrik - Test koneksi WhatsApp Gateway berhasil! 🚀');

        return back()->with('status', 'Pesan test telah dikirim ke ' . $target);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('super_admin'), 403);

        $allowed = self::allowedKeys();
        $allowedFlip = array_flip($allowed);

        $data = $request->validate([
            'rows' => ['required', 'array'],
            'rows.*.key' => ['required', 'string', 'max:255'],
            'rows.*.value' => ['nullable', 'string', 'max:65535'],
        ]);

        foreach ($data['rows'] as $row) {
            $key = $row['key'];
            if (! isset($allowedFlip[$key])) {
                continue;
            }

            if ($key === 'whatsapp.enabled' && ! in_array((string) ($row['value'] ?? ''), ['0', '1'], true)) {
                continue;
            }

            $setting = Setting::query()->where('setting_key', $key)->first();
            $name = $setting?->name ?? $key;
            $type = $setting?->type ?? (str_contains($key, 'template') ? 'longtext' : 'text');

            $this->settingStore->set($key, (string) ($row['value'] ?? ''), $name, $type);
        }

        return back()->with('status', 'Pengaturan WhatsApp disimpan.');
    }
}
