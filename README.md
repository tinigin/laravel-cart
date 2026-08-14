# Laravel Cart

A customizable Laravel package for adding shopping cart functionality to your Laravel applications.

**Packagist:** [tinigin/laravel-cart](https://packagist.org/packages/tinigin/laravel-cart)

[![PHP Version Require](https://img.shields.io/badge/php-%5E8.2-blue)]()
[![License](https://img.shields.io/badge/license-MIT-green)]()
[![Laravel](https://img.shields.io/badge/Laravel-9.0%2B-red)]()

- [Installation](#installation)
- [Configuration](#configuration)
- [Storage Options](#storage-options)
- [Quick Start](#quick-start)
- [API Reference](#api-reference)
- [Examples](#examples)
- [Octane Support](#octane-support)
- [Contributing](#contributing)
- [License](#license)

## Introduction

The `Laravel Cart` is a highly customizable and lightweight package that integrates shopping cart functionality into your Laravel application. It provides a simple yet powerful API for managing cart items, with support for both **database** and **session-based** storage. Perfect for e-commerce platforms that need flexible cart management.

<a name="features"></a>
## Features

- **Multiple Storage Drivers**: Choose between database (persistent) and session (temporary) storage
- **Simple API**: Clean and intuitive methods for managing cart items
- **Product Extras**: Store additional product information (color, size, custom attributes, etc.)
- **Cart Metadata**: Track total, currency, promo codes, and other metadata
- **User Tracking**: Automatic user ID tracking for authenticated users
- **Cart Expiration**: Automatic cleanup of expired carts (database storage)
- **Facade Support**: Easy access via Laravel Facade pattern
- **Extensible**: Simple storage driver interface allows custom implementations
- **Octane Compatible**: Fully compatible with Laravel Octane using scoped bindings
- **Easy Integration**: Minimal configuration required, works out of the box

<a name="installation"></a>
## Installation

You can install the package using Composer:

```bash
composer require tinigin/laravel-cart
```

Run the database migrations:

```bash
php artisan migrate
```

<a name="configuration"></a>
## Configuration

The package works with zero configuration by default (uses database storage). If you want to customize the storage type, create or update your `.env` file:

```env
# Choose storage type: 'db' (default) or 'session'
CART_STORAGE=db
```

Alternatively, publish and edit the config file:

```bash
php artisan vendor:publish --provider="Tinigin\LaravelCart\CartServiceProvider" --tag="config"
```

This will create `config/cart.php`:

```php
return [
    // Storage type: 'db' (database) or 'session'
    'storage' => env('CART_STORAGE', 'db'),

    // Database configuration
    'db' => [
        'table' => 'carts',
    ],

    // Session configuration
    'session' => [
        'key' => 'cart_data',
    ],
];
```

<a name="storage-options"></a>
## Storage Options

### Database Storage (Default)

Store cart data persistently in the database:

```env
CART_STORAGE=db
```

**Advantages:**
- ✅ Persistent storage
- ✅ Works across different devices/browsers
- ✅ Better for authenticated users
- ✅ Automatic user tracking
- ✅ Configurable expiration

**Use case:** E-commerce sites where users may abandon and return to their cart

### Session Storage

Store cart data in the user's session:

```env
CART_STORAGE=session
```

**Advantages:**
- ✅ Fast (no database queries)
- ✅ No database overhead
- ✅ Simple implementation
- ✅ Good for quick purchases

**Use case:** Quick purchase sites, temporary shopping sessions

<a name="quick-start"></a>
## Quick Start

### Using the Facade

```php
use Tinigin\LaravelCart\Facades\Cart;

// Add item to cart
Cart::add(productId: 1, quantity: 2);

// Add item with extra data
Cart::add(
    productId: 5,
    quantity: 1,
    extra: ['color' => 'red', 'size' => 'M']
);

// Get all items
$items = Cart::items();

// Get cart total
$total = Cart::total();

// Remove item
Cart::remove(productId: 1);

// Clear entire cart
Cart::clear();

// Get cart ID
$cartId = Cart::cartId();
```

### Using Dependency Injection

```php
use Tinigin\LaravelCart\Services\CartService;

public function addToCart(CartService $cartService)
{
    $cartService->add(productId: 10, quantity: 2);
    $items = $cartService->items();
    $total = $cartService->total();
}
```

<a name="api-reference"></a>
## API Reference

### Methods

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `add()` | `productId` (int), `quantity` (int, default: 1), `extra` (array, default: []) | void | Add item to cart |
| `remove()` | `productId` (int) | void | Remove item from cart |
| `clear()` | none | void | Clear entire cart |
| `items()` | none | array | Get all cart items |
| `total()` | none | float | Get cart total |
| `cartId()` | none | string | Get current cart ID |
| `getOrCreate()` | none | array | Get or create cart |

### Item Structure

```php
[
    'product_id' => 1,           // Product ID
    'quantity' => 2,             // Item quantity
    'extra' => [                 // Optional: Custom data
        'color' => 'red',
        'size' => 'M',
        'name' => 'Product Name',
        'price' => 29.99,
        // ... any custom fields
    ],
]
```

<a name="examples"></a>
## Examples

### Example 1: Simple E-Commerce Controller

```php
<?php

namespace App\Http\Controllers;

use Tinigin\LaravelCart\Facades\Cart;

class ProductController extends Controller
{
    public function addToCart(Product $product)
    {
        Cart::add(
            productId: $product->id,
            quantity: request('quantity', 1),
            extra: [
                'name' => $product->name,
                'price' => $product->price,
                'color' => request('color'),
                'size' => request('size'),
            ]
        );

        return redirect()->back()->with('success', 'Added to cart');
    }

    public function cart()
    {
        return view('cart.index', [
            'items' => Cart::items(),
            'total' => Cart::total(),
        ]);
    }

    public function removeFromCart(int $productId)
    {
        Cart::remove($productId);
        
        return redirect()->back()->with('success', 'Removed from cart');
    }
}
```

### Example 2: Switching Between Storage Types

The API remains the same regardless of storage type:

```php
// In .env
CART_STORAGE=session  // or 'db'

// Code works identically:
Cart::add(productId: 1, quantity: 2);
$items = Cart::items();
$total = Cart::total();
```

For more detailed examples, see [USAGE_GUIDE.md](USAGE_GUIDE.md) and [STORAGE_CONFIG.md](STORAGE_CONFIG.md).

<a name="octane-support"></a>
## Octane Support

This package is fully compatible with **Laravel Octane**! 

### How it works

The cart service uses `scoped` binding instead of `singleton`, ensuring:
- ✅ No state pollution between requests
- ✅ Each request gets a fresh CartService instance
- ✅ Safe for Octane's concurrent request handling
- ✅ Zero configuration needed

### Usage with Octane

No code changes required! Just use the cart as normal:

```php
use Tinigin\LaravelCart\Facades\Cart;

// Works perfectly with Octane
Cart::add(productId: 1, quantity: 2);
$items = Cart::items();
```

Both database and session storage work seamlessly with Octane. For more details, see [OCTANE_COMPATIBILITY.md](OCTANE_COMPATIBILITY.md).

<a name="contributing"></a>
## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

<a name="license"></a>
## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
