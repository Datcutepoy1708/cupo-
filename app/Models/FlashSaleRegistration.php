<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSaleRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'flash_sale_id',
        'seller_id',
        'product_id',
        'proposed_price',
        'proposed_quantity',
        'status',
        'rejection_reason',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'proposed_price' => 'decimal:2',
        'proposed_quantity' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    // Relationships

    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Helpers

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function canBeCancelledBy(User $seller): bool
    {
        return $this->seller_id === $seller->id
            && $this->isPending()
            && $this->flashSale->registration_deadline !== null
            && now()->lt($this->flashSale->registration_deadline);
    }
}
