<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'date_of_birth',
        'role',
        'status',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
        ];
    }

    // Relationships
    public function sellerProfile()
    {
        return $this->hasOne(SellerProfile::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function sellerOrders()
    {
        return $this->hasMany(SellerOrder::class, 'seller_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class, 'buyer_id');
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function followedShops()
    {
        return $this->belongsToMany(SellerProfile::class, 'shop_follows', 'user_id', 'seller_profile_id')
            ->withPivot('followed_at');
    }

    public function chatRooms()
    {
        if ($this->role === 'customer') {
            return $this->hasMany(ChatRoom::class, 'buyer_id');
        }

        if ($this->role === 'seller') {
            return $this->hasMany(ChatRoom::class, 'seller_id');
        }

        return $this->hasMany(ChatRoom::class, 'buyer_id')->whereRaw('1 = 0');
    }

    public function savedCoupons()
    {
        return $this->belongsToMany(Coupon::class, 'customer_coupons')
            ->withPivot(['id', 'status', 'used_at'])
            ->withTimestamps();
    }

    public function getCartCountAttribute(): int
    {
        return $this->cart?->items()->sum('quantity') ?? 0;
    }

    /**
     * URL ảnh đại diện — trả về ảnh thật nếu có,
     * fallback về UI Avatars (tự tạo ảnh chữ cái đầu tiên).
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/'.$this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name)
            .'&background=c62828&color=fff&size=128&bold=true';
    }
}
