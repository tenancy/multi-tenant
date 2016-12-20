<?php

namespace Hyn\Tenancy\Tests;

use Hyn\Tenancy\Providers\TenancyProvider;
use Hyn\Tenancy\Providers\WebserverProvider;
use Illuminate\Support\Arr;

class WebserverDisabledTest extends Test
{
    protected $loadProviders = [
        TenancyProvider::class
    ];

    /**
     * @test
     */
    public function webserver_provider_is_disabled()
    {
        $this->assertFalse(Arr::get($this->app->getLoadedProviders(), WebserverProvider::class, false));
    }
}
