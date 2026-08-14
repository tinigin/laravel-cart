<?php

namespace Tinigin\LaravelCart\Storage;

use Tinigin\LaravelCart\Models\Cart;
use Illuminate\Support\Facades\Auth;

class DatabaseCartStorage implements CartStorageInterface
{
    public function getOrCreate(string $cartId): array
    {
        $cart = Cart::firstOrCreate(
            ['cart_id' => $cartId],
            [
                'items' => [],
                'metadata' => [
                    'total' => 0,
                    'quantity' => 0
                ],
                'expires_at' => now()->addDays(7),
                'user_id' => Auth::id(),
            ]
        );

        return $this->cartToArray($cart);
    }

    public function update(string $cartId, array $data): void
    {
        Cart::where('cart_id', $cartId)->update($data);
    }

    public function getItems(string $cartId): array
    {
        $cart = Cart::where('cart_id', $cartId)->first();
        return $cart?->items ?? [];
    }

    public function addItem(string $cartId, array $item): void
    {
        $cart = Cart::where('cart_id', $cartId)->firstOrFail();
        $items = $cart->items ?? [];

        $existing = collect($items)->firstWhere('id', $item['id']);
        if ($existing) {
            $existing['quantity'] += $item['quantity'] ?? 1;
        } else {
            $items[] = $item;
        }

        $cart->update(['items' => $items]);
    }

    public function removeItem(string $cartId, int $id): void
    {
        $cart = Cart::where('cart_id', $cartId)->firstOrFail();
        $cart->items = collect($cart->items)
            ->reject(fn ($item) => $item['id'] === $id)
            ->values()
            ->toArray();
        $cart->save();
    }

    public function clear(string $cartId): void
    {
        $cart = Cart::where('cart_id', $cartId)->firstOrFail();
        $cart->update([
            'items' => [],
            'metadata' => [
                'total' => 0,
                'quantity' => 0
            ],
        ]);
    }

    public function getMetadata(string $cartId): array
    {
        $cart = Cart::where('cart_id', $cartId)->first();
        return $cart?->metadata ?? [];
    }

    public function updateMetadata(string $cartId, array $metadata): void
    {
        Cart::where('cart_id', $cartId)->update(['metadata' => $metadata]);
    }

    public function delete(string $cartId): void
    {
        Cart::where('cart_id', $cartId)->delete();
    }

    public function exists(string $cartId): bool
    {
        return Cart::where('cart_id', $cartId)->exists();
    }

    private function cartToArray(Cart $cart): array
    {
        return [
            'cart_id' => $cart->cart_id,
            'items' => $cart->items ?? [],
            'metadata' => $cart->metadata ?? [],
            'user_id' => $cart->user_id,
            'expires_at' => $cart->expires_at,
        ];
    }
}

