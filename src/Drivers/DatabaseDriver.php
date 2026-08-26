<?php

namespace Tinigin\LaravelCart\Drivers;

use Tinigin\LaravelCart\Contracts\CartDriver;
use Tinigin\LaravelCart\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DatabaseDriver implements CartDriver
{
    protected ?Cart $cachedCart = null;

    protected function getCartModel(string $cartId): Cart
    {
        if ($this->cachedCart && $this->cachedCart->cart_id === $cartId) {
            return $this->cachedCart;
        }

        $this->cachedCart = Cart::where('cart_id', $cartId)->first();

        // Если корзина существует и срок истёк – удаляем
        if ($this->cachedCart && $this->cachedCart->expires_at && $this->cachedCart->expires_at < now()) {
            $this->cachedCart->delete();
            $this->cachedCart = null;
        }

        $expiresIn = config('cart.expires_in_minutes', 60 * 24 * 7);

        if (!$this->cachedCart) {
            // Создаём новую корзину
            $this->cachedCart = Cart::create([
                'cart_id'   => $cartId,
                'items'     => [],
                'metadata'  => [
                    'quantity'   => 0,
                ],
                'user_id'   => Auth::check() ? Auth::id() : null,
                'expires_at'=> now()->addMinutes($expiresIn),
            ]);

        } else {
            // Обновляем срок при каждом обращении, если корзина не пустая и привязываем пользователя
            if ($this->cachedCart->items)
                $this->cachedCart->expires_at = now()->addMinutes($expiresIn);

            if (Auth::check() && $this->cachedCart->user_id !== Auth::id()) {
                $this->cachedCart->user_id = Auth::id();
            }

            $this->cachedCart->save();
        }

        return $this->cachedCart;
    }

    public function get(string $cartId): array
    {
        return $this->getCartModel($cartId)->items ?? [];
    }

    public function getCart(string $cartId): ?object
    {
        return $this->getCartModel($cartId);
    }

    public function addItem(string $cartId, array $item): void
    {
        $cart = $this->getCartModel($cartId);
        DB::transaction(function () use ($cart, $item) {
            $items = $cart->items;
            $found = false;
            foreach ($items as $key => $existing) {
                if ($existing['id'] == $item['id']) {
                    $items[$key]['quantity'] += $item['quantity'] ?? 1;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $items[] = $item;
            }
            $cart->items = $items;
            $cart->save();
        });
        $this->cachedCart = null;
    }

    public function getItem(string $cartId, string $itemId): ?array
    {
        $cart = $this->getCartModel($cartId);

        foreach ($cart->items as $item) {
            if ($item['id'] == $itemId) {
                return $item;
            }
        }

        return null;
    }

    public function removeItem(string $cartId, string $itemId): void
    {
        $cart = $this->getCartModel($cartId);
        DB::transaction(function () use ($cart, $itemId) {
            $items = array_filter($cart->items, fn($item) => $item['id'] != $itemId);
            $cart->items = array_values($items);
            $cart->save();
        });
        $this->cachedCart = null;
    }

    public function updateItem(string $cartId, string $itemId, int|float $quantity): void
    {
        $cart = $this->getCartModel($cartId);
        DB::transaction(function () use ($cart, $itemId, $quantity) {
            $items = $cart->items;
            foreach ($items as $key => $item) {
                if ($item['id'] == $itemId) {
                    $items[$key]['quantity'] = $quantity;
                    break;
                }
            }
            $cart->items = $items;
            $cart->save();
        });
        $this->cachedCart = null;
    }

    public function clear(string $cartId): void
    {
        $cart = $this->getCartModel($cartId);
        DB::transaction(function () use ($cart) {
            $cart->items = [];
            $cart->metadata = [];
            $cart->save();
        });
        $this->cachedCart = null;
    }

    public function exists(string $cartId): bool
    {
        return Cart::where('cart_id', $cartId)->exists();
    }

    public function hasItem(string $cartId, string $itemId): bool
    {
        $items = $this->get($cartId);
        foreach ($items as $item) {
            if ($item['id'] == $itemId) {
                return true;
            }
        }
        return false;
    }

    public function total(string $cartId): float
    {
        $cart = $this->getCartModel($cartId);
        return $cart->metadata['total'] ?? 0;
    }

    public function getAllIds(string $cartId): array
    {
        $ids = [];

        $items = $this->get($cartId);
        if ($items) {
            foreach ($items as $item) {
                if (isset($item['type']) && $item['type'] == 'set') {
                    foreach ($item['items'] as $p) {
                        $ids[] = $p['id'];
                    }
                } else {
                    $ids[] = $item['id'];
                }
            }
        }

        return $ids;
    }
}