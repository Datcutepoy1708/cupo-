<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    public const CACHE_KEY = 'cupo_settings';

    /**
     * Lay tat ca settings tu cache (key => value)
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::all()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Lay gia tri cua 1 key cai dat kem default fallback
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    /**
     * Lay tat ca settings thuoc 1 group (key => value)
     */
    public function getGroup(string $group): array
    {
        return Setting::inGroup($group)->pluck('value', 'key')->toArray();
    }

    /**
     * Cap nhat hoac tao moi 1 key cai dat
     */
    public function set(string $key, mixed $value, string $group = 'general', string $type = 'text'): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'type' => $type,
            ]
        );

        $this->clearCache();
    }

    /**
     * Cap nhat nhieu settings cung luc trong 1 transaction va tu dong xu ly upload anh
     */
    public function updateMany(array $data, array $files = []): void
    {
        DB::transaction(function () use ($data, $files) {
            // 1. Xu ly upload file anh (neu co)
            foreach ($files as $key => $file) {
                if ($file && $file->isValid()) {
                    // Xoa anh cu neu ton tai
                    $oldPath = $this->get($key);
                    if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }

                    $path = $file->store('settings', 'public');
                    $data[$key] = $path;
                }
            }

            // 2. Cap nhat vao CSDL
            foreach ($data as $key => $value) {
                Setting::where('key', $key)->update([
                    'value' => is_bool($value) ? ($value ? '1' : '0') : $value,
                ]);
            }
        });

        $this->clearCache();
    }

    /**
     * Xoa cache settings de nap lai
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
