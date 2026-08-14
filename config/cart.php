<?php

return [
    // Выбор хранилища: 'db' (база данных) или 'session' (сессия)
    'storage' => env('CART_STORAGE', 'db'),

    // Настройки для хранилища БД
    'db' => [
        'table' => 'cart',
    ],

    // Настройки для хранилища сессии
    'session' => [
        'key' => 'cart_data',
    ],
];
