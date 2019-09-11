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

namespace Hyn\Tenancy\Providers\Tenants;

use Hyn\Tenancy\Generators;
use Hyn\Tenancy\Listeners;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class EventProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $subscribe = [
        // Manages databases for tenants.
        Generators\Webserver\Database\DatabaseGenerator::class,
        // Manages the connections for the tenants.
        Listeners\Database\ConnectsTenants::class,
        // Runs migrations for new tenants.
        Listeners\Database\MigratesTenants::class,
        // Runs the seeds after the first migration
        Listeners\Database\SeedsTenants::class,
        // Sets the connection used to manage tenant.
        Listeners\Database\FillsConnectionUsed::class,
        // Manages the directories for the tenants.
        Generators\Filesystem\DirectoryGenerator::class,
        // Sets the uuid value on a website based on tenancy configuration.
        Listeners\WebsiteUuidGeneration::class,
        // Loads custom configuration folder for tenant.
        Listeners\Filesystem\LoadsConfigs::class,
        // Adds tenant specific routes.
        Listeners\Filesystem\LoadsRoutes::class,
        // Loads custom translation folder for tenant.
        Listeners\Filesystem\LoadsTranslations::class,
        // Loads custom vendor folder for tenant.
        Listeners\Filesystem\LoadsVendor::class,
        // Loads custom views folder for tenant.
        Listeners\Filesystem\LoadsViews::class,
        // Activates a disk to be used in filesystem actions.
        Listeners\Filesystem\ActivatesDisk::class,
        // Forces the app url and url generator to use tenant fqdn.
        Listeners\URL\UpdateAppUrl::class,
    ];

    public function boot()
    {
        foreach ($this->subscribe as $listener) {
            $this->app[Dispatcher::class]->subscribe($listener);
        }
    }

    public function register()
    {
        // ..
    }
}
