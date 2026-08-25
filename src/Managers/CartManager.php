<?php

namespace Tinigin\LaravelCart\Managers;

use Tinigin\LaravelCart\Contracts\CartDriver;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartManager
{
    protected CartDriver $driver;
    protected string $cartId;

    public function __construct(CartDriver $driver)
    {
        $this->driver = $driver;
        $this->cartId = $this->resolveCartId();
    }

    protected function resolveCartId(): string
    {
        $cookieName = config('cart.cookie.name', 'cart_id');
        $lifetime = config('cart.cookie.lifetime', 60 * 24 * 365); // минуты

        $cartId = Cookie::get($cookieName);
        if (!$cartId) {
            $cartId = (string) Str::uuid();
            // Создаём защищённое cookie (шифрование + подпись)
            Cookie::queue(
                Cookie::make(
                    $cookieName,
                    $cartId,
                    $lifetime,
                    '/',
                    null,
                    config('session.secure', false),
                    true, // httpOnly
                    false,
                    'lax'
                )
            );
        }
        return $cartId;
    }

    public function getItems(): array
    {
        return $this->driver->get($this->cartId);
    }

    public function add(array $item): void
    {
        $this->driver->addItem($this->cartId, $item);
    }

    public function remove(string $itemId): void
    {
        $this->driver->removeItem($this->cartId, $itemId);
    }

    public function update(string $itemId, int $quantity): void
    {
        $this->driver->updateItem($this->cartId, $itemId, $quantity);
    }

    public function clear(): void
    {
        $this->driver->clear($this->cartId);
    }

    public function has(string $itemId): bool
    {
        return $this->driver->hasItem($this->cartId, $itemId);
    }

    public function total(): float
    {
        return $this->driver->total($this->cartId);
    }

    public function count(): int
    {
        return count($this->getItems());
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}