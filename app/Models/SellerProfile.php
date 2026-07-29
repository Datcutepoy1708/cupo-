<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_name',
        'slug',
        'logo',
        'banner',
        'description',
        'address',
        'commission_rate',
        'balance',
        'bank_name',
        'bank_account',
        'bank_owner',
        'status',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
