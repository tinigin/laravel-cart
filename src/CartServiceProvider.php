<?php

namespace Tinigin\LaravelCart;

use Illuminate\Support\ServiceProvider;
use Tinigin\LaravelCart\Services\CartService;

class CartServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    	$this->mergeConfigFrom(__DIR__.'/../config/cart.php', 'cart');
		
        // Use 'scoped' instead of 'singleton' for Octane compatibility
        // This ensures a new CartService instance per request, preventing state pollution
        $this->app->scoped('laravel-cart', fn () => new CartService());
    }

    public function boot(): void
    {
    	// Publish Config
        $this->publishes([
            __DIR__.'/../config/cart.php' => config_path('cart.php'),
        ], 'config');

        // Publish Migrations
		$this->publishesMigrations([
			__DIR__.'/../src/Database/Migrations' => database_path('migrations'),
		], 'migrations');
    }
}
