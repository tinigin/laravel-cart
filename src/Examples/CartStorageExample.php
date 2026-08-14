<?php

namespace Tinigin\LaravelCart\Examples;

/**
 * Example usage of Cart with different storage types
 *
 * This file demonstrates how to use the cart module
 * with database and session storage.
 */

class CartStorageExample
{
    /**
     * Example 1: Using Database Storage (Default)
     *
     * In .env:
     * CART_STORAGE=db
     *
     * Database storage persists cart data in the database.
     * Cart ID is tracked in session.
     */
    public static function exampleDatabaseStorage(): void
    {
        $cartService = app('laravel-cart');

        // Add items to cart (stored in DB)
        $cartService->add(productId: 1, quantity: 2);
        $cartService->add(productId: 5, quantity: 1, extra: ['size' => 'M']);

        // Retrieve items from DB
        $items = $cartService->items();
        // Output:
        // [
        //     ['product_id' => 1, 'quantity' => 2, 'extra' => []],
        //     ['product_id' => 5, 'quantity' => 1, 'extra' => ['size' => 'M']],
        // ]

        // Get total from DB
        $total = $cartService->total();  // Calculated from items

        // Cart persists across browser sessions
        // Data expires after 7 days (configurable)
    }

    /**
     * Example 2: Using Session Storage
     *
     * In .env:
     * CART_STORAGE=session
     *
     * Session storage keeps cart data in user's session.
     * No database queries needed.
     */
    public static function exampleSessionStorage(): void
    {
        $cartService = app('laravel-cart');

        // Add items to cart (stored in session)
        $cartService->add(productId: 1, quantity: 2);
        $cartService->add(productId: 5, quantity: 1, extra: ['color' => 'red']);

        // Retrieve items from session
        $items = $cartService->items();
        // Output:
        // [
        //     ['product_id' => 1, 'quantity' => 2, 'extra' => []],
        //     ['product_id' => 5, 'quantity' => 1, 'extra' => ['color' => 'red']],
        // ]

        // Get total from session
        $total = $cartService->total();

        // Cart is cleared when session expires
        // No database storage needed
    }

    /**
     * Example 3: Using Facade (Recommended)
     *
     * Using the Cart facade for cleaner syntax.
     */
    public static function exampleUsingFacade(): void
    {
        use Tinigin\LaravelCart\Facades\Cart;

        // Add item
        Cart::add(productId: 10, quantity: 1);

        // Get all items
        $items = Cart::items();

        // Get total
        $total = Cart::total();

        // Remove item
        Cart::remove(productId: 10);

        // Clear cart
        Cart::clear();

        // Get cart ID
        $cartId = Cart::cartId();
    }

    /**
     * Example 4: Working with Extra Data
     *
     * You can store additional product information
     * in the 'extra' field of each item.
     */
    public static function exampleWithExtraData(): void
    {
        $cartService = app('laravel-cart');

        // Add product with extra data
        $cartService->add(
            productId: 42,
            quantity: 3,
            extra: [
                'name' => 'Red T-Shirt',
                'price' => 29.99,
                'color' => 'red',
                'size' => 'L',
                'sku' => 'TS-RED-L',
            ]
        );

        // Retrieve cart items
        $items = $cartService->items();
        // Output:
        // [
        //     [
        //         'product_id' => 42,
        //         'quantity' => 3,
        //         'extra' => [
        //             'name' => 'Red T-Shirt',
        //             'price' => 29.99,
        //             'color' => 'red',
        //             'size' => 'L',
        //             'sku' => 'TS-RED-L',
        //         ],
        //     ],
        // ]

        // Use the extra data in your frontend
        foreach ($items as $item) {
            echo $item['extra']['name'] . ' x' . $item['quantity'];
            echo ' - $' . ($item['extra']['price'] * $item['quantity']);
        }
    }

    /**
     * Example 5: Switching Storage Dynamically
     *
     * For advanced use cases where you need to switch
     * storage type at runtime.
     */
    public static function exampleDynamicStorage(): void
    {
        use Tinigin\LaravelCart\Services\CartService;
        use Tinigin\LaravelCart\Storage\StorageFactory;

        // Use factory to get configured storage
        $storage = StorageFactory::make();  // Uses CART_STORAGE env var

        // Or explicitly choose storage
        $dbStorage = StorageFactory::make('db');
        $sessionStorage = StorageFactory::make('session');

        // Create service with specific storage
        $cartService = new CartService(storage: $sessionStorage);

        $cartService->add(productId: 1, quantity: 1);
        $items = $cartService->items();
    }

    /**
     * Example 6: Cart in a Controller
     *
     * Practical example of using cart in a Laravel controller.
     */
    public static function controllerExample(): string
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers;

use Tinigin\LaravelCart\Facades\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function add(int $productId, int $quantity = 1): RedirectResponse
    {
        Cart::add($productId, $quantity);

        return redirect()->back()
            ->with('success', 'Product added to cart');
    }

    public function view(): View
    {
        return view('cart.index', [
            'items' => Cart::items(),
            'total' => Cart::total(),
        ]);
    }

    public function remove(int $productId): RedirectResponse
    {
        Cart::remove($productId);

        return redirect()->back()
            ->with('success', 'Product removed from cart');
    }

    public function clear(): RedirectResponse
    {
        Cart::clear();

        return redirect()->back()
            ->with('success', 'Cart cleared');
    }
}
PHP;
    }

    /**
     * Example 7: Configuration Scenarios
     *
     * Different configuration setups for different scenarios.
     */
    public static function configurationScenarios(): string
    {
        return <<<'TEXT'
SCENARIO 1: E-Commerce Site
- Use DATABASE storage
- CART_STORAGE=db
- Benefit: Persistent cart, works across devices, better for checkout flow
- Users can abandon and return to cart later

SCENARIO 2: Quick Purchase Site
- Use SESSION storage
- CART_STORAGE=session
- Benefit: Fast, no DB overhead, simple
- Users complete purchase in one session

SCENARIO 3: High Traffic Site
- Use SESSION storage initially
- CART_STORAGE=session
- Benefit: Scales better with high concurrent users
- Consider implementing cart recovery system

SCENARIO 4: Mobile App Integration
- Use DATABASE storage
- CART_STORAGE=db
- Benefit: Sync across devices/sessions
- User can switch between mobile and web

SCENARIO 5: Guest Users
- Mix of both (advanced)
- Use session storage for guests
- Use database storage for authenticated users
- Implement migration when user logs in
TEXT;
    }
}

