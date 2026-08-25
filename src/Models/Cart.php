<?php

namespace Tinigin\LaravelCart\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'carts';

    protected $fillable = [
        'cart_id', 'items', 'metadata', 'user_id', 'expires_at'
    ];

    protected $casts = [
        'items' => 'array',
        'metadata' => 'array',
        'expires_at' => 'datetime',
    ];

    public function recalculateTotal(): void
    {
        $total = 0;
        $quantity = 0;
        $metadata = $this->metadata ?? [];

        foreach ($this->items as $item) {
            $total += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
            $quantity += $item['quantity'] ?? 0;
        }

        $metadata['total'] = $total;
        $metadata['quantity'] = $quantity;

        $this->metadata = $metadata;
    }

    public function extendExpiry(int $minutes): void
    {
        $this->expires_at = now()->addMinutes($minutes);
        $this->save();
    }
}