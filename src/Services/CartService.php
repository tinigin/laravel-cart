<?php

namespace Tinigin\LaravelCart\Services;

use Tinigin\LaravelCart\Models\Cart;
use Tinigin\LaravelCart\Storage\CartStorageInterface;
use Tinigin\LaravelCart\Storage\StorageFactory;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CartService
{
    private ?array $cart = null;
    private string $cartIdCookieKey = 'cart_id';
    private CartStorageInterface $storage;

    public function __construct(
        private string $sessionKey = 'cart_id',
        CartStorageInterface $storage = null
    ) {
        $this->storage = $storage ?? StorageFactory::make();
    }

    public function getOrCreate(): array
    {
        if ($this->cart) {
            return $this->cart;
        }

        $cartId = $this->resolveCartId();
        $this->cart = $this->storage->getOrCreate($cartId);

        return $this->cart;
    }

    private function resolveCartId(): string
    {
        // Приоритет: сессия -> cookie -> новый ID
        $fromSession = session()->get($this->sessionKey);
        if ($fromSession) {
            return $fromSession;
        }

        // Cookie (для Octane/безсессионных сценариев можно хранить только cookie)
        $cookie = request()->cookie($this->cartIdCookieKey);
        if ($cookie) {
            session()->put($this->sessionKey, $cookie);
            return $cookie;
        }

        $newId = Cart::generateCartId();
        session()->put($this->sessionKey, $newId);

        // В Octane можно сразу ставить cookie на ответ, но здесь только подготовка
        return $newId;
    }

    public function add(int $id, int $quantity = 1, string $type = 'product', array $extra = []): void
    {
        $cart = $this->getOrCreate();
        $cartId = $cart['cart_id'];

        $item = [
            'id' => $id,
            'type' => $type,
            'quantity' => $quantity,
            'extra' => $extra,
        ];

        $this->storage->addItem($cartId, $item);
        $this->recalculateTotal($cartId);
        $this->cart = null; // Reset cache
    }

    public function remove(int $id): void
    {
        $cart = $this->getOrCreate();
        $cartId = $cart['cart_id'];

        $this->storage->removeItem($cartId, $id);
        $this->recalculateTotal($cartId);
        $this->cart = null; // Reset cache
    }

    public function clear(): void
    {
        $cart = $this->getOrCreate();
        $this->storage->clear($cart['cart_id']);
        $this->cart = null; // Reset cache
    }

    public function items(): array
    {
        $cart = $this->getOrCreate();
        return $this->storage->getItems($cart['cart_id']);
    }

    public function has(int $id): bool
    {
        $cart = $this->getOrCreate();
        return $this->storage->hasItem($cart['cart_id'], $id);
    }

    public function get(int $id): array
    {
        $cart = $this->getOrCreate();
        return $this->storage->getItem($cart['cart_id'], $id);
    }

    public function total(): float
    {
        $cart = $this->getOrCreate();
        $metadata = $this->storage->getMetadata($cart['cart_id']);
        return (float) ($metadata['total'] ?? 0);
    }

    private function recalculateTotal(string $cartId): void
    {
        // Здесь подставь свою логику получения цен (Product::find, API, кэш и т. д.)
        // Для примера считаем total = quantity * 100
        $items = $this->storage->getItems($cartId);
        $total = collect($items)->sum(fn ($i) => ($i['quantity'] ?? 1) * 100);

        $metadata = $this->storage->getMetadata($cartId);
        $metadata['total'] = (float) $total;

        $this->storage->updateMetadata($cartId, $metadata);
    }

    public function cartId(): string
    {
        return $this->getOrCreate()['cart_id'];
    }

    public function setCookieOnResponse($response): void
    {
        // Вызывается в middleware или контроллере, чтобы установить cookie
        $id = $this->cartId();
        $response->headers->setCookie(
            \Symfony\Component\HttpFoundation\Cookie::create(
                $this->cartIdCookieKey,
                $id,
                now()->addDays(30)->getTimestamp(),
                '/',
                config('session.domain'),
                false, // secure — true в продакшене
                true  // httpOnly
            )
        );
    }
}
