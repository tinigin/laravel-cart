<?php

namespace Tinigin\LaravelCart\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Cart extends Model
{
    protected $fillable = [
        'cart_id',
        'items',
        'metadata',
        'user_id',
        'expires_at',
    ];

    protected $casts = [
        'items' => 'array',
        'metadata' => 'array',
    ];

    public static function generateCartId(): string
    {
        return Str::uuid()->toString();
    }
}
