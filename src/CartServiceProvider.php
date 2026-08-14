<?php

namespace Tinigin\LaravelCart;

use Illuminate\Support\ServiceProvider;
use Tinigin\LaravelCart\Services\CartService;

class CartServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Use 'scoped' instead of 'singleton' for Octane compatibility
        // This ensures a new CartService instance per request, preventing state pollution
        $this->app->scoped('laravel-cart', fn () => new CartService());
    }

    public function boot(): void
    {}
}
