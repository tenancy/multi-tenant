<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Tests\Webserver;

use Hyn\Tenancy\Providers\TenancyProvider;
use Hyn\Tenancy\Providers\WebserverProvider;
use Hyn\Tenancy\Tests\Test;
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
