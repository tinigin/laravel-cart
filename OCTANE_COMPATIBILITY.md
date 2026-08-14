# Laravel Octane Compatibility

## Scoped Binding

The cart service is registered using `scoped` binding instead of `singleton`. This is essential for Laravel Octane compatibility.

### Why `scoped` instead of `singleton`?

**Problem with `singleton`:**
- Creates ONE instance that persists across ALL requests in Octane
- Cart data from one request can leak into another request
- User A's cart could appear in User B's request
- Session cache (`$this->cart`) keeps stale data across requests

**Solution with `scoped`:**
- Creates a NEW instance per request scope
- Each request gets its own isolated CartService
- No state pollution between requests
- Safe for both traditional Laravel and Octane

### How it works

```php
// CartServiceProvider.php
$this->app->scoped('laravel-cart', fn () => new CartService());
```

Each request in Octane gets:
- A fresh CartService instance
- A new storage driver instance (DatabaseCartStorage or SessionCartStorage)
- Clean internal cache (`$this->cart = null`)

### Storage Driver Behavior

Both storage drivers are stateless and work perfectly with Octane:

**DatabaseCartStorage**
- No internal state, always queries the database
- Each request gets fresh data from DB
- Safe for Octane ✅

**SessionCartStorage**
- Uses Laravel's `Session` facade which is request-aware
- Each request has its own session store
- Safe for Octane ✅

### Usage with Octane

No code changes needed! The cart API remains the same:

```php
use Tinigin\LaravelCart\Facades\Cart;

// Works perfectly in Octane
Cart::add(productId: 1, quantity: 2);
$items = Cart::items();
```

The scoped binding ensures proper isolation automatically.

### Performance Considerations

**Octane with Database Storage**
- Database queries are cached within the request (via `$this->cart`)
- Multiple calls to `items()`, `total()` use cached data
- Minimal performance impact

**Octane with Session Storage**
- Session data is stored in memory per request
- Very fast access to cart data
- Ideal for high-performance scenarios

## Configuration for Octane

No special configuration is needed. Just ensure you're using:

```env
# Works great with Octane
CART_STORAGE=db
# or
CART_STORAGE=session
```

The `scoped` binding handles everything automatically.

## Testing with Octane

To test cart functionality with Octane:

```bash
# Start Octane server
php artisan octane:start

# Run tests
php artisan test

# or with specific test file
php artisan test tests/Feature/CartTest.php
```

The scoped binding ensures tests work correctly even with Octane's concurrent request handling.

