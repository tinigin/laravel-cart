<?php

namespace Tinigin\LaravelCart\Drivers;

use Tinigin\LaravelCart\Contracts\CartDriver;
use Illuminate\Support\Facades\Session;

class SessionDriver implements CartDriver
{
    public function get(string $cartId): array
    {
        return Session::get("cart_{$cartId}", []);
    }

    public function getCart(string $cartId): ?object
    {
        // Для совместимости возвращаем объект с items и metadata
        $data = $this->get($cartId);
        return (object) [
            'items' => $data,
            'metadata' => Session::get("cart_metadata_{$cartId}", [
                'quantity'   => 0,
            ]),
        ];
    }

    public function addItem(string $cartId, array $item): void
    {
        $items = $this->get($cartId);
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
        Session::put("cart_{$cartId}", $items);
    }

    public function getItem(string $cartId, string $itemId): ?array
    {
        $items = $this->get($cartId);
        foreach ($items as $item) {
            if ($item['id'] == $itemId) {
                return $item;
            }
        }
        return null;
    }

    public function removeItem(string $cartId, string $itemId): void
    {
        $items = $this->get($cartId);
        $items = array_filter($items, fn($item) => $item['id'] != $itemId);
        Session::put("cart_{$cartId}", array_values($items));
    }

    public function updateItem(string $cartId, string $itemId, int|float $quantity): void
    {
        $items = $this->get($cartId);
        foreach ($items as $key => $item) {
            if ($item['id'] == $itemId) {
                $items[$key]['quantity'] = $quantity;
                break;
            }
        }
        Session::put("cart_{$cartId}", $items);
    }

    public function clear(string $cartId): void
    {
        Session::forget("cart_{$cartId}");
        Session::forget("cart_metadata_{$cartId}");
    }

    public function exists(string $cartId): bool
    {
        return Session::has("cart_{$cartId}");
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
        $metadata = Session::get("cart_metadata_{$cartId}", ['total' => 0]);
        return $metadata['total'];
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