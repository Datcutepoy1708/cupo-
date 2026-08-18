<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'amount',
        'bank_name',
        'bank_account',
        'bank_owner',
        'status',
        'admin_note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Đã từ chối',
            default => $this->status,
        };
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'withdrawal-badge-pending',
            'approved' => 'withdrawal-badge-approved',
            'rejected' => 'withdrawal-badge-rejected',
            default => '',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0, ',', '.').'đ';
    }
}
