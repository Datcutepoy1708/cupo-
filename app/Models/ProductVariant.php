<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'sale_price',
        'stock',
        'image_path',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
    ];

    protected $appends = [
        'is_on_sale',
        'discount_percentage',
        'current_price',
        'image_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        return asset('storage/'.ltrim($this->image_path, '/'));
    }

    public function getIsOnSaleAttribute(): bool
    {
        $product = $this->relationLoaded('product') ? $this->product : $this->product()->first();
        if ($product && $product->is_flash_sale) {
            return true;
        }

        return ! is_null($this->sale_price) && $this->sale_price > 0 && $this->sale_price < $this->price;
    }

    public function getDiscountPercentageAttribute(): int
    {
        $product = $this->relationLoaded('product') ? $this->product : $this->product()->first();
        if ($product && $product->is_flash_sale) {
            return (int) ($product->flash_sale_info['discount_percentage'] ?? 0);
        }

        if ($this->is_on_sale && $this->price > 0) {
            return (int) round((($this->price - $this->sale_price) / $this->price) * 100);
        }

        return 0;
    }

    public function getCurrentPriceAttribute(): float
    {
        $product = $this->relationLoaded('product') ? $this->product : $this->product()->first();
        if ($product && $product->is_flash_sale) {
            $fsDiscount = (int) ($product->flash_sale_info['discount_percentage'] ?? 0);
            if ($fsDiscount > 0) {
                return (float) (round(($this->price * (100 - $fsDiscount) / 100) / 1000) * 1000);
            }
        }

        return $this->is_on_sale ? (float) $this->sale_price : (float) $this->price;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
