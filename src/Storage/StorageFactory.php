<?php

namespace Tinigin\LaravelCart\Storage;

use InvalidArgumentException;

class StorageFactory
{
    public static function make(string $driver = null): CartStorageInterface
    {
        $driver = $driver ?? config('cart.storage', 'db');

        return match ($driver) {
            'db', 'database' => new DatabaseCartStorage(),
            'session' => new SessionCartStorage(),
            default => throw new InvalidArgumentException("Unknown cart storage driver: {$driver}"),
        };
    }
}

