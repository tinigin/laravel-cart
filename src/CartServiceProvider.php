<?php

namespace Tinigin\LaravelCart;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Tinigin\LaravelCart\Drivers\DatabaseDriver;
use Tinigin\LaravelCart\Drivers\SessionDriver;
use Tinigin\LaravelCart\Managers\CartManager;
use Tinigin\LaravelCart\Console\Commands\CleanCarts;

class CartServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    	$this->mergeConfigFrom(__DIR__.'/../config/cart.php', 'cart');

        $this->app->scoped(CartManager::class, function ($app) {
            Log::channel('stack')->info('Cart driver: ' . config('cart.driver'));
            $driver = config('cart.driver', 'database');
            $driverInstance = match ($driver) {
                'database' => new DatabaseDriver(),
                'session'  => new SessionDriver(),
                default    => throw new \InvalidArgumentException("Unsupported cart driver: $driver"),
            };
            return new CartManager($driverInstance);
        });

        $this->app->alias(CartManager::class, 'laravel-cart');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/cart.php' => config_path('cart.php'),
            ], 'config');

            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->commands([
                CleanCarts::class,
            ]);
        }
    }
}
