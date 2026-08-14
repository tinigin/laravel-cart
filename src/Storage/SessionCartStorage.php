<?php

namespace Tinigin\LaravelCart\Storage;

use Illuminate\Support\Facades\Session;

class SessionCartStorage implements CartStorageInterface
{
    private string $sessionKey;

    public function __construct()
    {
        $this->sessionKey = config('cart.session.key', 'cart_data');
    }

    public function getOrCreate(string $cartId): array
    {
        $carts = Session::get($this->sessionKey, []);

        if (!isset($carts[$cartId])) {
            $carts[$cartId] = [
                'cart_id' => $cartId,
                'items' => [],
                'metadata' => [
                    'total' => 0,
                    'quantity' => 0,
                ],
                'user_id' => auth()->id(),
                'expires_at' => now()->addDays(7)->toDateTimeString(),
            ];
            Session::put($this->sessionKey, $carts);
        }

        return $carts[$cartId];
    }

    public function update(string $cartId, array $data): void
    {
        $carts = Session::get($this->sessionKey, []);

        if (isset($carts[$cartId])) {
            $carts[$cartId] = array_merge($carts[$cartId], $data);
            Session::put($this->sessionKey, $carts);
        }
    }

    public function getItems(string $cartId): array
    {
        $carts = Session::get($this->sessionKey, []);
        return $carts[$cartId]['items'] ?? [];
    }

    public function addItem(string $cartId, array $item): void
    {
        $carts = Session::get($this->sessionKey, []);

        if (!isset($carts[$cartId])) {
            $this->getOrCreate($cartId);
            $carts = Session::get($this->sessionKey, []);
        }

        $items = $carts[$cartId]['items'] ?? [];
        $existing = collect($items)->firstWhere('id', $item['id']);

        if ($existing) {
            $existing['quantity'] += $item['quantity'] ?? 1;
        } else {
            $items[] = $item;
        }

        $carts[$cartId]['items'] = $items;
        Session::put($this->sessionKey, $carts);
    }

    public function removeItem(string $cartId, int $id): void
    {
        $carts = Session::get($this->sessionKey, []);

        if (isset($carts[$cartId])) {
            $carts[$cartId]['items'] = collect($carts[$cartId]['items'] ?? [])
                ->reject(fn ($item) => $item['id'] === $id)
                ->values()
                ->toArray();
            Session::put($this->sessionKey, $carts);
        }
    }

    public function clear(string $cartId): void
    {
        $carts = Session::get($this->sessionKey, []);

        if (isset($carts[$cartId])) {
            $carts[$cartId] = [
                'cart_id' => $cartId,
                'items' => [],
                'metadata' => [
                    'total' => 0,
                    'quantity' => 0
                ],
                'user_id' => auth()->id(),
                'expires_at' => now()->addDays(7)->toDateTimeString(),
            ];
            Session::put($this->sessionKey, $carts);
        }
    }

    public function getMetadata(string $cartId): array
    {
        $carts = Session::get($this->sessionKey, []);
        return $carts[$cartId]['metadata'] ?? [];
    }

    public function updateMetadata(string $cartId, array $metadata): void
    {
        $carts = Session::get($this->sessionKey, []);

        if (isset($carts[$cartId])) {
            $carts[$cartId]['metadata'] = $metadata;
            Session::put($this->sessionKey, $carts);
        }
    }

    public function delete(string $cartId): void
    {
        $carts = Session::get($this->sessionKey, []);
        unset($carts[$cartId]);
        Session::put($this->sessionKey, $carts);
    }

    public function exists(string $cartId): bool
    {
        $carts = Session::get($this->sessionKey, []);
        return isset($carts[$cartId]);
    }
}

