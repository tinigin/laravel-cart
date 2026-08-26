<?php

namespace Tinigin\LaravelCart\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getItems()
 * @method static void add(array $item)
 * @method static array get(string $itemId)
 * @method static void remove(string $itemId)
 * @method static void update(string $itemId, int $quantity)
 * @method static void clear()
 * @method static bool has(string $itemId)
 * @method static float total()
 * @method static int count()
 * @method static bool isEmpty()
 * @method static array getAllIds()
 */
class Cart extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-cart';
    }
}