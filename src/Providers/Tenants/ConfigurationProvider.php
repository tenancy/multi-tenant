<?php

namespace Hyn\Tenancy\Providers\Tenants;

use Illuminate\Support\ServiceProvider;

class ConfigurationProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../assets/configs/tenancy.php',
            'tenancy'
        );
        $this->publishes([
            __DIR__ . '/../../assets/configs/tenancy.php' => config_path('tenancy.php')
        ], 'tenancy');
    }
}
