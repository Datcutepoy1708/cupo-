<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
    ];

    /**
     * Scope query lay theo nhom
     */
    public function scopeInGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
