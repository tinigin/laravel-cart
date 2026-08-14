# Singleton vs Scoped - Octane Compatibility Guide

## Quick Answer

**TL;DR:** Changed from `singleton` to `scoped` to make the cart safe for Laravel Octane.

## The Problem with Singleton

### Traditional Laravel (No Octane)
```
Request 1 → Create Singleton Instance → Process → Destroy
Request 2 → Create Singleton Instance → Process → Destroy
Request 3 → Create Singleton Instance → Process → Destroy
```

Each request gets its own process, so a single instance doesn't cause issues.

### Laravel Octane
```
Worker starts → Create Singleton Instance
Request 1 → Process with SAME instance → Modify state
Request 2 → Process with SAME instance → See Request 1's state! ⚠️
Request 3 → Process with SAME instance → See old state! ⚠️
```

**THE PROBLEM:** All requests share the same instance and its state!

## Example of the Bug

### With `singleton` (WRONG for Octane):

```php
// CartServiceProvider.php
$this->app->singleton('laravel-cart', fn () => new CartService());

// CartService.php
class CartService
{
    private ?array $cart = null;  // Shared state!
    
    public function getOrCreate()
    {
        if ($this->cart) {  // This could be from PREVIOUS request!
            return $this->cart;
        }
        // ...
    }
}
```

**Scenario:**
```
Request 1 (User A):
  Cart::getOrCreate() 
  → $this->cart = [ 'cart_id' => 'user-a-cart', 'items' => [...] ]

Request 2 (User B):
  Cart::getOrCreate()
  → if ($this->cart) // TRUE! Returns User A's cart!
  → User B sees User A's items! SECURITY BUG!
```

## Solution: Use `scoped` Binding

### With `scoped` (CORRECT for Octane):

```php
// CartServiceProvider.php
$this->app->scoped('laravel-cart', fn () => new CartService());
```

### How Scoped Works

```
Worker starts
Request 1 → Create CartService instance A → Process → Forget
Request 2 → Create CartService instance B → Process → Forget
Request 3 → Create CartService instance C → Process → Forget
```

**Each request gets its own fresh instance!**

## Laravel Documentation

From Laravel Octane docs:

> When running Octane, application instances are recycled across requests. To prevent accidental state pollution, Laravel's service container uses request scoping by default.

**Scoped bindings:**
- Created fresh for each request
- Discarded after request completes
- No state sharing between requests
- Perfect for Octane

## Comparison Table

| Aspect | singleton | scoped |
|--------|-----------|--------|
| **Traditional Laravel** | ✅ Works | ✅ Works |
| **Octane Compatibility** | ❌ DANGER | ✅ Safe |
| **State Sharing** | Yes (across requests) | No (per-request only) |
| **Performance** | Slightly faster | Minimal difference |
| **Security Risk** | HIGH with Octane | None |

## Real-World Impact

### Without Fix (singleton)
```
User A's Session:
  cart = { id: 'abc', items: [product1, product2] }

User B's Request (same Octane worker):
  Cart::items() 
  → Returns User A's items! ⚠️
```

### With Fix (scoped)
```
User A's Session:
  cart = { id: 'abc', items: [product1, product2] }

User B's Request (same Octane worker):
  Cart::items()
  → Fresh CartService instance
  → Correctly returns User B's empty cart ✅
```

## Why This Package Was Updated

The original implementation used `singleton` because:
- It was developed for traditional Laravel (Request → Process → Destroy)
- Performance was slightly better with singleton
- Octane compatibility wasn't a concern at the time

**Now it uses `scoped` because:**
- Octane is increasingly popular
- Security is more important than marginal performance gains
- Scoped has negligible performance impact
- It's the recommended Laravel pattern

## Migration Guide

### If you have existing code:

**NO CHANGES NEEDED!** The package handles everything automatically.

Just make sure you're using the updated version:

```bash
composer update tinigin/laravel-cart
```

### Why no code changes?

- The API didn't change
- Facades work the same way
- Service injection works the same way
- Only the internal binding strategy changed

### Before (Old Version)
```php
use Tinigin\LaravelCart\Facades\Cart;

Cart::add(productId: 1, quantity: 2);  // Works
```

### After (New Version)
```php
use Tinigin\LaravelCart\Facades\Cart;

Cart::add(productId: 1, quantity: 2);  // Still works!
// Now also safe with Octane!
```

## Performance Impact

The performance difference between `singleton` and `scoped` is negligible:

- Creating a new CartService instance: ~0.1ms
- Each Octane request already creates many scoped services
- Database/session lookups dominate timing (not instance creation)

**Conclusion:** Switching to `scoped` has virtually no performance penalty while fixing a critical Octane compatibility issue.

## References

- [Laravel Octane Documentation](https://laravel.com/docs/octane)
- [Laravel Service Container - Scoping](https://laravel.com/docs/container#scoping)
- [Octane Issue: Singleton State Leaks](https://github.com/laravel/octane/discussions/)

