<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::pluck('value', 'setting_key')->toArray();
        return view('modules.settings.landing', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_title' => 'nullable|string',
            'hero_desc' => 'nullable|string',
            'tagline' => 'nullable|string',
            'nama_bimbel' => 'nullable|string',
            'about_us' => 'nullable|string',
            'registration_terms' => 'nullable|string',
            'footer_address' => 'nullable|string',
            'footer_phone1' => 'nullable|string',
            'footer_phone2' => 'nullable|string',
            'footer_email' => 'nullable|string|email',
            'footer_web' => 'nullable|string|url',
            'whatsapp_number' => 'nullable|string',
            'landing_faq' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['value' => $value, 'name' => ucwords(str_replace('_', ' ', $key))]
            );
        }

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = 'logo-bimbel.png';
            $logo->move(public_path('image'), $logoName);
            Setting::updateOrCreate(
                ['setting_key' => 'logo_filename'],
                ['value' => $logoName, 'name' => 'Logo Filename']
            );
        }
        
        if ($request->hasFile('hero_image')) {
            $hero = $request->file('hero_image');
            $heroName = 'hero.png';
            $hero->move(public_path('image'), $heroName);
            Setting::updateOrCreate(
                ['setting_key' => 'hero_filename'],
                ['value' => $heroName, 'name' => 'Hero Filename']
            );
        }

        for ($i = 1; $i <= 4; $i++) {
            if ($request->hasFile("gallery_{$i}")) {
                $gallery = $request->file("gallery_{$i}");
                $galleryName = "gallery_{$i}_" . time() . '.' . $gallery->getClientOriginalExtension();
                $gallery->move(public_path('image'), $galleryName);
                Setting::updateOrCreate(
                    ['setting_key' => "gallery_{$i}"],
                    ['value' => $galleryName, 'name' => "Gallery {$i}"]
                );
            }
        }

        return back()->with('status', 'Pengaturan landing page berhasil disimpan.');
    }
}
