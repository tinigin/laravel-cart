# Quick Start Guide - Cart Storage

## 1. Basic Setup

After installation, no additional configuration is needed. The module defaults to database storage.

## 2. Choose Your Storage Type

### Option A: Database Storage (Default)

```bash
# In your .env file
CART_STORAGE=db
```

**Pros:**
- Persistent data
- Works across devices
- Better for authenticated users

**Cons:**
- Requires database queries
- Slightly slower

### Option B: Session Storage

```bash
# In your .env file
CART_STORAGE=session
```

**Pros:**
- Fast (no DB queries)
- Simple to use
- No database overhead

**Cons:**
- Data lost when session expires
- Only works for current browser/device

## 3. Usage (Same API for Both)

```php
<?php

namespace App\Http\Controllers;

use Tinigin\LaravelCart\Facades\Cart;

class CartController extends Controller
{
    public function addToCart()
    {
        // Add 2 units of product ID 5
        Cart::add(productId: 5, quantity: 2);
        
        return redirect()->back()->with('success', 'Added to cart');
    }

    public function viewCart()
    {
        $items = Cart::items();
        $total = Cart::total();
        
        return view('cart.index', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function removeFromCart($productId)
    {
        Cart::remove($productId);
        
        return redirect()->back()->with('success', 'Removed from cart');
    }

    public function clearCart()
    {
        Cart::clear();
        
        return redirect()->back()->with('success', 'Cart cleared');
    }
}
```

## 4. Configuration Options

### Default Configuration (config/cart.php)

```php
return [
    // Choose storage type
    'storage' => env('CART_STORAGE', 'db'),

    // Database storage
    'db' => [
        'table' => 'carts',
    ],

    // Session storage
    'session' => [
        'key' => 'cart_data',
    ],
];
```

## 5. Advanced: Manually Specify Storage

If you need more control, you can manually instantiate the service with a specific storage:

```php
use Tinigin\LaravelCart\Services\CartService;
use Tinigin\LaravelCart\Storage\DatabaseCartStorage;
use Tinigin\LaravelCart\Storage\SessionCartStorage;

// Force database storage
$cartService = new CartService(storage: new DatabaseCartStorage());

// Force session storage
$cartService = new CartService(storage: new SessionCartStorage());
```

## 6. Available Methods

All storage types support the same API:

```php
// Get or create cart
$cart = Cart::getOrCreate();  // Returns array

// Add item
Cart::add(productId: 1, quantity: 2, extra: []);

// Get all items
$items = Cart::items();  // Returns array

// Get total
$total = Cart::total();  // Returns float

// Remove item
Cart::remove(productId: 1);

// Clear cart
Cart::clear();

// Get cart ID
$cartId = Cart::cartId();  // Returns string
```

## 7. Item Structure

Each item in the cart has the following structure:

```php
[
    'product_id' => 1,           // Required: Product ID
    'quantity' => 2,             // Required: Quantity
    'extra' => [                 // Optional: Extra data
        'color' => 'red',
        'size' => 'M',
        // ... any custom fields
    ],
]
```

## 8. Migration Between Storage Types

To migrate from one storage type to another:

1. Update `CART_STORAGE` in `.env`
2. For database → session: Data will be lost (session storage doesn't migrate old data)
3. For session → database: Old session data won't be in DB (start fresh)

If you need to preserve data, implement a migration command in your application.

## 9. Performance Tips

- **Database storage**: Index the `cart_id` column for faster queries
- **Session storage**: Configure session timeout based on your needs
- **Mixed approach**: Use session for anonymous users, database for authenticated users

## 10. Troubleshooting

**Issue: Cart data not persisting**
- Check if session storage is being used (check `.env`)
- For database storage, ensure migrations have been run
- Check user authentication status for user_id tracking

**Issue: Cart has old data**
- For session storage, clear browser cookies/cache
- For database storage, check cart expiration time

**Issue: Performance is slow**
- With database storage, add database indexes
- Consider switching to session storage if appropriate
- Use caching strategies for product information

