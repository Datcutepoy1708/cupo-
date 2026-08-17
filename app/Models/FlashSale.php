<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'starts_at',
        'registration_deadline',
        'ends_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'registration_deadline' => 'datetime',
        'ends_at' => 'datetime',
        'status' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(FlashSaleProduct::class);
    }

    public function registrations()
    {
        return $this->hasMany(FlashSaleRegistration::class);
    }

    /**
     * Kiem tra phien co dang trong thoi gian mo dang ky khong.
     */
    public function isRegistrationOpen(): bool
    {
        if ($this->registration_deadline === null) {
            return false;
        }

        return now()->lt($this->registration_deadline);
    }

    public function scopeLive($query)
    {
        return $query->where('status', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', true)
            ->where('starts_at', '>', now());
    }

    public function getExecutionStatusAttribute(): string
    {
        if (! $this->status) {
            return 'disabled';
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'expired';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'upcoming';
        }

        return 'live';
    }
}
