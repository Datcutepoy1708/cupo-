<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingCarrier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'logo',
        'base_fee',
        'estimated_days',
        'tracking_url_template',
        'hotline',
        'description',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'base_fee' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function sellerOrders(): HasMany
    {
        return $this->hasMany(SellerOrder::class, 'carrier_id');
    }

    public function shippingLogs(): HasMany
    {
        return $this->hasMany(OrderShippingLog::class, 'carrier_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
