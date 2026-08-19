<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'seller_id',
        'sub_total',
        'shipping_fee',
        'discount_amount',
        'grand_total',
        'commission_amount',
        'status',
        'tracking_number',
        'carrier_id',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function carrier()
    {
        return $this->belongsTo(ShippingCarrier::class, 'carrier_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function dispute()
    {
        return $this->hasOne(Dispute::class);
    }

    public function shippingLogs()
    {
        return $this->hasMany(OrderShippingLog::class, 'seller_order_id')->orderBy('event_time', 'desc');
    }
}
