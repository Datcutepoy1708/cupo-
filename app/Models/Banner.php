<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'image_path',
        'link_url',
        'position',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected $appends = [
        'image_url',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => static::clearBannerCache());
        static::deleted(fn () => static::clearBannerCache());
    }

    public static function clearBannerCache(): void
    {
        Cache::forget('banners:homepage_hero');
        Cache::forget('banners:homepage_mid');
        Cache::forget('banners:sidebar');
        Cache::forget('banners:category_top');
    }

    public function getImageUrlAttribute(): string
    {
        $rawPath = $this->image_path;
        if (empty($rawPath)) {
            return '';
        }
        if (Str::startsWith($rawPath, ['http://', 'https://', '//'])) {
            return $rawPath;
        }
        if (Str::contains($rawPath, '/storage/')) {
            return asset('storage/' . explode('/storage/', $rawPath)[1]);
        }
        return asset('storage/' . ltrim($rawPath, '/'));
    }

    // Scope: Lấy các banner đang active và trong thời gian hiển thị
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc');
    }

    // Scope: Lọc theo vị trí hiển thị
    public function scopeAtPosition(Builder $query, string $position): Builder
    {
        return $query->where('position', $position);
    }
}
