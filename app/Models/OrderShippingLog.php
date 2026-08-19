<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderShippingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_order_id',
        'carrier_id',
        'status',
        'title',
        'location',
        'description',
        'event_time',
    ];

    protected $casts = [
        'event_time' => 'datetime',
    ];

    public function sellerOrder(): BelongsTo
    {
        return $this->belongsTo(SellerOrder::class, 'seller_order_id');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(ShippingCarrier::class, 'carrier_id');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'order_placed' => 'bg-secondary text-white',
            'preparing' => 'bg-info text-dark',
            'picked_up' => 'bg-primary text-white',
            'sorting_hub' => 'bg-warning text-dark',
            'delivering' => 'bg-indigo text-white',
            'delivered' => 'bg-success text-white',
            'failed' => 'bg-danger text-white',
            default => 'bg-light text-dark',
        };
    }
}
