<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'total_item_amount',
        'total_shipping_fee',
        'total_discount',
        'grand_total',
        'payment_method',
        'payment_status',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'notes',
    ];

    protected $casts = [
        'total_item_amount' => 'decimal:2',
        'total_shipping_fee' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sellerOrders()
    {
        return $this->hasMany(SellerOrder::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
