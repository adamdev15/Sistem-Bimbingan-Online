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

        $allowed = self::allowedKeys();
        $settings = Setting::query()
            ->whereIn('setting_key', $allowed)
            ->orderBy('setting_key')
            ->get();

        $placeholdersHelp = [
            'wa.template.siswa.invoice_created' => ':nama, :biaya, :nominal, :due_date, :inv',
            'wa.template.siswa.payment_due_tomorrow' => ':nama, :biaya, :nominal, :due_date, :inv',
            'wa.template.siswa.payment_success' => ':nama, :biaya, :nominal, :due_date, :inv',
            'wa.template.siswa.class_schedule' => ':nama, :mapel, :hari, :jam, :cabang',
            'wa.template.siswa.class_reminder' => ':nama, :mapel, :hari, :jam, :cabang',
            'wa.template.tutor.class_schedule' => ':nama, :mapel, :hari, :jam, :cabang',
            'wa.template.tutor.class_reminder' => ':nama, :mapel, :hari, :jam, :cabang',
            'wa.template.tutor.salary_paid' => ':nama, :periode, :nominal, :status',
            'wa.template.admin.payment_received' => ':nama_siswa, :biaya, :nominal, :inv, :cabang',
        ];

        return view('modules.whatsapp-settings.edit', [
            'settings' => $settings,
            'placeholdersHelp' => $placeholdersHelp,
        ]);
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
