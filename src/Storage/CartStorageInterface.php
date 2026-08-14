<?php

namespace Tinigin\LaravelCart\Storage;

interface CartStorageInterface
{
    /**
     * Получить или создать корзину
     */
    public function getOrCreate(string $cartId): array;

    /**
     * Обновить данные корзины
     */
    public function update(string $cartId, array $data): void;

    /**
     * Получить все товары в корзине
     */
    public function getItems(string $cartId): array;

    /**
     * Добавить товар в корзину
     */
    public function addItem(string $cartId, array $item): void;

    /**
     * Удалить товар из корзины
     */
    public function removeItem(string $cartId, int $id): void;

    /**
     * Очистить корзину
     */
    public function clear(string $cartId): void;

    /**
     * Получить метаданные корзины
     */
    public function getMetadata(string $cartId): array;

    /**
     * Обновить метаданные корзины
     */
    public function updateMetadata(string $cartId, array $metadata): void;

    /**
     * Удалить корзину
     */
    public function delete(string $cartId): void;

    /**
     * Проверить существование корзины
     */
    public function exists(string $cartId): bool;
}

