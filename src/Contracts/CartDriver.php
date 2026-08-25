<?php

namespace Tinigin\LaravelCart\Contracts;

interface CartDriver
{
    public function get(string $cartId): array;
    public function getCart(string $cartId): ?object;
    public function addItem(string $cartId, array $item): void;
    public function removeItem(string $cartId, string $itemId): void;
    public function updateItem(string $cartId, string $itemId, int $quantity): void;
    public function clear(string $cartId): void;
    public function exists(string $cartId): bool;
    public function hasItem(string $cartId, string $itemId): bool;
    public function total(string $cartId): float;
}