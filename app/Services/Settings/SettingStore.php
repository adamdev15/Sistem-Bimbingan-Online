<?php

namespace App\Services\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SettingStore
{
    private const CACHE_PREFIX = 'setting:';

    private const CACHE_TTL_SECONDS = 120;

    public function get(string $key, ?string $default = null): ?string
    {
        if (! Schema::hasTable('settings')) {
            return $default;
        }

        return Cache::remember(self::CACHE_PREFIX.$key, self::CACHE_TTL_SECONDS, function () use ($key, $default) {
            $row = Setting::query()->where('setting_key', $key)->first();

            return $row !== null ? $row->value : $default;
        });
    }

    public function set(string $key, ?string $value, ?string $name = null, string $type = 'text'): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        Setting::query()->updateOrCreate(
            ['setting_key' => $key],
            [
                'value' => $value,
                'name' => $name ?? $key,
                'type' => $type,
            ]
        );

        Cache::forget(self::CACHE_PREFIX.$key);
    }

    public function bool(string $key, bool $default = false): bool
    {
        $v = $this->get($key, $default ? '1' : '0');

        return in_array((string) $v, ['1', 'true', 'yes', 'on'], true);
    }

    public function forgetCache(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX.$key);
    }
}
