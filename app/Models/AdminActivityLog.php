<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AdminActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getModuleBadgeClassAttribute(): string
    {
        return match ($this->module) {
            'withdrawals' => 'bg-success text-white',
            'sellers' => 'bg-primary text-white',
            'products' => 'bg-info text-dark',
            'disputes' => 'bg-danger text-white',
            'shipping' => 'bg-indigo text-white',
            'coupons' => 'bg-warning text-dark',
            'settings' => 'bg-dark text-white',
            'roles' => 'bg-purple text-white',
            'auth' => 'bg-secondary text-white',
            default => 'bg-light text-dark',
        };
    }

    public function getModuleLabelAttribute(): string
    {
        return match ($this->module) {
            'withdrawals' => 'Tài chính & Rút tiền',
            'sellers' => 'Gian hàng & Seller',
            'products' => 'Sản phẩm',
            'disputes' => 'Tranh chấp & Khiếu nại',
            'shipping' => 'Vận chuyển & Đối tác',
            'coupons' => 'Mã giảm giá',
            'settings' => 'Cấu hình hệ thống',
            'roles' => 'Phân quyền & Chức vụ',
            'auth' => 'Xác thực & Bảo mật',
            default => ucfirst($this->module),
        };
    }

    public function isSensitive(): bool
    {
        return in_array($this->action, [
            'approve_withdrawal',
            'reject_withdrawal',
            'block_seller',
            'reject_seller',
            'refund_dispute',
            'update_settings',
            'roles.manage',
        ]);
    }
}
