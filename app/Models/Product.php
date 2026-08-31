<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

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
        return ! is_null($this->sale_price) && $this->sale_price > 0 && $this->sale_price < $this->price;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if ($this->is_on_sale && $this->price > 0) {
            return (int) round((($this->price - $this->sale_price) / $this->price) * 100);
        }

        return 0;
    }

    public function getCurrentPriceAttribute(): float
    {
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
