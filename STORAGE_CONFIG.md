# Cart Storage Configuration

## Overview

The Laravel Cart module now supports multiple storage options for cart data:
- **Database (db)** - Store cart data in the database (default)
- **Session** - Store cart data in the user's session

## Configuration

Update your `.env` file to choose the storage type:

```env
# Use 'db' for database storage (default)
CART_STORAGE=db

# Or use 'session' for session-based storage
CART_STORAGE=session
```

## Configuration File

The configuration file (`config/cart.php`) contains storage-specific settings:

```php
return [
    // Choose storage: 'db' (database) or 'session' (user session)
    'storage' => env('CART_STORAGE', 'db'),

    // Database storage settings
    'db' => [
        'table' => 'carts',
    ],

    // Session storage settings
    'session' => [
        'key' => 'cart_data',  // Session key for storing cart data
    ],
];
```

## Usage Examples

### Using Database Storage (Default)

```php
use Tinigin\LaravelCart\Facades\Cart;

// Add item to cart
Cart::add(productId: 1, quantity: 2);

// Get cart items
$items = Cart::items();

// Get cart total
$total = Cart::total();

// Remove item
Cart::remove(productId: 1);

// Clear entire cart
Cart::clear();
```

The cart data is stored in the database and persists even if the user closes their browser or logs out.

### Using Session Storage

Set `CART_STORAGE=session` in your `.env` file. The usage API remains the same:

```php
use Tinigin\LaravelCart\Facades\Cart;

// Add item to cart
Cart::add(productId: 1, quantity: 2);

// Get cart items
$items = Cart::items();

// Get cart total
$total = Cart::total();

// Remove item
Cart::remove(productId: 1);

// Clear entire cart
Cart::clear();
```

With session storage, cart data is stored in the user's session and is cleared when the session expires.

## Switching Between Storage Types

You can easily switch between storage types without changing your code. The storage implementation is abstracted behind the `CartStorageInterface`, so the API remains consistent.

### Key Differences

| Feature | Database | Session |
|---------|----------|---------|
| Persistence | Permanent (until expiration) | Session duration |
| User Tracking | Automatic (user_id) | Automatic (via session) |
| Scalability | Better for high traffic | Good for small-medium traffic |
| Performance | Slightly slower (DB queries) | Faster (in-memory) |
| Expiration | Configurable (7 days default) | Session expiration time |

## Architecture

The storage system uses the Strategy pattern:

- **CartStorageInterface** - Defines the storage contract
- **DatabaseCartStorage** - Implements database-backed storage
- **SessionCartStorage** - Implements session-backed storage
- **StorageFactory** - Factory class that instantiates the correct storage driver

This design allows for easy addition of new storage drivers in the future (e.g., Redis, Cache).

## Advanced: Custom Storage Driver

To create a custom storage driver:

1. Create a class implementing `CartStorageInterface`
2. Implement all required methods
3. Register it in `StorageFactory::make()` method
4. Update your `.env` with the new driver name

Example:

```php
use Tinigin\LaravelCart\Storage\CartStorageInterface;

class RedisCartStorage implements CartStorageInterface
{
    // Implement all methods...
}
```

Then update `StorageFactory::make()`:

```php
public static function make(string $driver = null): CartStorageInterface
{
    $driver = $driver ?? config('cart.storage', 'db');

    return match ($driver) {
        'db', 'database' => new DatabaseCartStorage(),
        'session' => new SessionCartStorage(),
        'redis' => new RedisCartStorage(),
        default => throw new InvalidArgumentException("Unknown cart storage driver: {$driver}"),
    };
}
```

## Migration Notes

When switching from database to session storage or vice versa:

- Session storage does not persist data across different browsers/devices
- Database storage requires the cart ID to be tracked (via session or cookie)
- Existing database carts will not be migrated automatically

