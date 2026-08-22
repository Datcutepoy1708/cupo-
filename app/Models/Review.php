<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'comment',
        'status',
        'is_reported',
        'report_reason',
        'report_status',
        'admin_note',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_reported' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function reply()
    {
        return $this->hasOne(ReviewReply::class);
    }

    public function isReported(): bool
    {
        return $this->is_reported && $this->report_status === 'pending';
    }

    public function hasReply(): bool
    {
        return $this->reply()->exists();
    }
}
