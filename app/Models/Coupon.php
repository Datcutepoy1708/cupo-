<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'status' => 'boolean',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function savedUsers()
    {
        return $this->belongsToMany(User::class, 'customer_coupons')
            ->withPivot(['id', 'status', 'used_at'])
            ->withTimestamps();
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isAvailable(): bool
    {
        return $this->status
            && ! $this->isExpired()
            && ($this->usage_limit == 0 || $this->used_count < $this->usage_limit);
    }

    public function isFreeShipping(): bool
    {
        return $this->type === 'free_shipping';
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'fixed_amount' => 'Số tiền cố định',
            'percentage' => 'Phần trăm (%)',
            'free_shipping' => 'Miễn phí vận chuyển (Freeship)',
            default => $this->type,
        };
    }

    public function scopeActive($query)
    {
        return $query->where('status', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }
}
