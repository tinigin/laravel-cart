<?php

namespace Tinigin\LaravelCart\Console\Commands;

use Illuminate\Console\Command;
use Tinigin\LaravelCart\Models\Cart;

class CleanCarts extends Command
{
    protected $signature = 'cart:clean';
    protected $description = 'Delete expired carts from database';

    public function handle()
    {
        $count = Cart::where('expires_at', '<', now())->delete();
        $this->info("Deleted {$count} expired carts.");
    }
}