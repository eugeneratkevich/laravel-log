<?php

namespace Merkeleon\Log\Providers;

use Illuminate\Support\ServiceProvider;

class MerkeleonLogProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__) . '/config/merkeleon_log.php' => config_path('merkeleon_log.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/config/merkeleon_log.php', 'merkeleon_log'
        );
    }
}