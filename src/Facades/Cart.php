<?php

namespace Tinigin\LaravelCart\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Tinigin\LaravelCart\Services\CartService instance()
 * @method static array items()
 * @method static float total()
 * @method static void add(int $id, int $quantity = 1, string $type = 'product', array $extra = [])
 * @method static void remove(int $id)
 * @method static void clear()
 * @method static string cartId()
 * @method static void setCookieOnResponse($response)
 */
class Cart extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-cart';
    }
}

