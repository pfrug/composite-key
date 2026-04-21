<?php

namespace Pfrug\CompositeKey;

use Illuminate\Support\ServiceProvider;

class CompositeKeyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/composite-key.php', 'composite-key');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/composite-key.php' => config_path('composite-key.php'),
        ], 'composite-key-config');
    }
}
