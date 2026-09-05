<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'price',
        'sale_price',
        'has_variants',
        'stock',
        'thumbnail',
        'description',
        'short_description',
        'attributes',
        'status',
        'admin_note',
        'views_count',
        'likes_count',
    ];

    protected $casts = [
        'has_variants' => 'boolean',
        'attributes' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
        'views_count' => 'integer',
        'likes_count' => 'integer',
    ];

    protected $appends = [
        'is_on_sale',
        'discount_percentage',
        'current_price',
        'thumbnail_url',
        'min_price',
        'max_price',
        'price_range_display',
        'is_flash_sale',
        'flash_sale_info',
    ];

    /**
     * Cache map danh sách sản phẩm thuộc các phiên Flash Sale đang Live.
     */
    public static function getActiveFlashSaleMap(): array
    {
        return Cache::remember('active_flash_sale_map', 10, function () {
            $liveSales = FlashSale::live()->with('products')->get();
            if ($liveSales->isEmpty()) {
                return [];
            }
            $map = [];
            foreach ($liveSales as $liveSale) {
                foreach ($liveSale->products as $fsp) {
                    if (! isset($map[$fsp->product_id])) {
                        $map[$fsp->product_id] = [
                            'flash_sale_id' => $fsp->flash_sale_id,
                            'flash_sale_price' => (float) $fsp->flash_sale_price,
                            'quantity_limit' => (int) $fsp->quantity_limit,
                            'quantity_sold' => (int) $fsp->quantity_sold,
                            'starts_at' => $liveSale->starts_at?->toISOString(),
                            'ends_at' => $liveSale->ends_at?->toISOString(),
                            'ends_at_timestamp' => $liveSale->ends_at ? $liveSale->ends_at->timestamp * 1000 : null,
                            'session_name' => $liveSale->name,
                        ];
                    }
                }
            }
            return $map;
        });
    }

    /**
     * Lấy thông tin Flash Sale đang áp dụng cho sản phẩm này (nếu có).
     */
    public function getFlashSaleInfoAttribute(): ?array
    {
        $map = self::getActiveFlashSaleMap();
        if (! isset($map[$this->id])) {
            return null;
        }

        $data = $map[$this->id];
        $hasVars = $this->has_variants && $this->relationLoaded('variants') && $this->variants->isNotEmpty();

        if ($hasVars) {
            $cheapest = $this->variants->sortBy('price')->first();
            $origPrice = (float) ($cheapest->price ?? $this->price);
        } else {
            $origPrice = (float) $this->price;
        }

        $salePrice = (float) $data['flash_sale_price'];
        if ($this->price > 0 && $salePrice >= $origPrice) {
            $pctDiscount = round((($this->price - $salePrice) / $this->price) * 100);
            if ($pctDiscount >= 10) {
                $salePrice = round(($origPrice * (100 - $pctDiscount) / 100) / 1000) * 1000;
            }
        }

        $discountPct = $origPrice > 0 ? (int) round((($origPrice - $salePrice) / $origPrice) * 100) : 0;

        return [
            'is_active' => true,
            'price' => $salePrice,
            'original_price' => $origPrice,
            'discount_percentage' => $discountPct,
            'ends_at' => $data['ends_at'],
            'ends_at_timestamp' => $data['ends_at_timestamp'],
            'quantity_limit' => $data['quantity_limit'],
            'quantity_sold' => $data['quantity_sold'],
            'session_name' => $data['session_name'],
        ];
    }

    public function getIsFlashSaleAttribute(): bool
    {
        return $this->flash_sale_info !== null;
    }

    public function getMinPriceAttribute(): float
    {
        if ($this->has_variants && $this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            return (float) $this->variants->min('current_price');
        }

        return (float) $this->current_price;
    }

    public function getMaxPriceAttribute(): float
    {
        if ($this->has_variants && $this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            return (float) $this->variants->max('current_price');
        }

        return (float) $this->current_price;
    }

    public function getPriceRangeDisplayAttribute(): string
    {
        if ($this->has_variants && $this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            $min = $this->variants->min('current_price');
            $max = $this->variants->max('current_price');
            if ($min !== null && $max !== null && $min != $max) {
                return number_format($min, 0, ',', '.') . '₫ - ' . number_format($max, 0, ',', '.') . '₫';
            }
        }

        return number_format($this->current_price, 0, ',', '.') . '₫';
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (! $this->thumbnail) {
            return null;
        }

        if (str_starts_with($this->thumbnail, 'http://') || str_starts_with($this->thumbnail, 'https://')) {
            return $this->thumbnail;
        }

        return asset('storage/'.ltrim($this->thumbnail, '/'));
    }

    public function getIsOnSaleAttribute(): bool
    {
        if ($this->is_flash_sale) {
            return true;
        }

        return ! is_null($this->sale_price) && $this->sale_price > 0 && $this->sale_price < $this->price;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if ($this->is_flash_sale) {
            return (int) ($this->flash_sale_info['discount_percentage'] ?? 0);
        }

        if ($this->is_on_sale && $this->price > 0) {
            return (int) round((($this->price - $this->sale_price) / $this->price) * 100);
        }

        return 0;
    }

    public function getCurrentPriceAttribute(): float
    {
        if ($this->is_flash_sale) {
            return (float) ($this->flash_sale_info['price'] ?? $this->price);
        }

        return $this->is_on_sale ? (float) $this->sale_price : (float) $this->price;
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
