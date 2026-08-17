<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerSupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'category',
        'subject',
        'message',
        'attachment',
        'status',
        'admin_response',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'account_blocked' => 'Kháng nghị khóa tài khoản',
            'withdrawal_issue' => 'Sự cố rút tiền',
            'product_rejected' => 'Kháng nghị duyệt sản phẩm',
            'commission_fee' => 'Thắc mắc hoa hồng & phí sàn',
            default => 'Khác / Hỗ trợ chung',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Mới mở',
            'in_review' => 'Đang xử lý',
            'resolved' => 'Đã giải quyết',
            'closed' => 'Đã đóng',
            default => $this->status,
        };
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'open' => 'ticket-badge-open',
            'in_review' => 'ticket-badge-review',
            'resolved' => 'ticket-badge-resolved',
            'closed' => 'ticket-badge-closed',
            default => '',
        };
    }
}
