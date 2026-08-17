<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_order_id',
        'buyer_id',
        'reason',
        'evidence_images',
        'status',
        'admin_decision',
    ];

    protected $casts = [
        'evidence_images' => 'array',
    ];

    public function sellerOrder()
    {
        return $this->belongsTo(SellerOrder::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Nhan vien Label tieng Viet tuong ung voi status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Chờ xử lý',
            'in_progress' => 'Đang xử lý',
            'refunded' => 'Đã hoàn tiền',
            'rejected' => 'Đã từ chối',
            default => $this->status,
        };
    }

    /**
     * CSS class tuong ung voi status (dung trong badge).
     */
    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'dispute-badge-pending',
            'in_progress' => 'dispute-badge-progress',
            'refunded' => 'dispute-badge-refunded',
            'rejected' => 'dispute-badge-rejected',
            default => '',
        };
    }
}
