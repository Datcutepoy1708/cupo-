<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_name',
        'business_type',
        'slug',
        'logo',
        'banner',
        'description',
        'address',
        'national_id',
        'commission_rate',
        'balance',
        'bank_name',
        'bank_account',
        'bank_owner',
        'status',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'national_id' => 'encrypted',
            'commission_rate' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}