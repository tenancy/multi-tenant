<?php

namespace Hyn\Tenancy\Providers;

use Illuminate\Support\ServiceProvider;

class WebserverProvider extends ServiceProvider
{
    public function register()
    {
        // Sets file access as wide as possible, ignoring server masks.
        umask(0);
        $this->registerConfiguration();
    }

    protected function registerConfiguration()
    {
        $this->publishes([
            __DIR__ . '/../../assets/configs/tenancy.php' => config_path('tenancy.php')
        ], 'tenancy');
    }
}
