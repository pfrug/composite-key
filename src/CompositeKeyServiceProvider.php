<?php

namespace Pfrug\CompositeKey;

use Illuminate\Support\ServiceProvider;

/**
 * Registers the package configuration and exposes it for publishing.
 *
 * Merges the bundled `composite-key.php` defaults into the host app's config
 * and publishes the file under the `composite-key-config` tag so integrators
 * can override values such as the route-key separator.
 */
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
