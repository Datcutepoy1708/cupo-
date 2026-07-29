<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'price',
        'has_variants',
        'stock',
        'thumbnail',
        'description',
        'short_description',
        'attributes',
        'status',
        'admin_note',
        'views_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'has_variants' => 'boolean',
        'attributes' => 'array',
        'views_count' => 'integer',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
