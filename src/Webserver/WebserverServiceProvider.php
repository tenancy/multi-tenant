<?php

namespace Hyn\Webserver;

use Hyn\MultiTenant\Contracts\WebsiteRepositoryContract;
use Hyn\MultiTenant\Models\Website;
use Hyn\Webserver\Models\SslCertificate;
use Hyn\Webserver\Models\SslHostname;
use Hyn\Webserver\Repositories\SslRepository;
use Illuminate\Support\ServiceProvider;

class WebserverServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function boot()
    {

        // configuration
        $this->mergeConfigFrom(__DIR__.'/../../config/webserver.php', 'webserver');
        $this->publishes([__DIR__.'/../../config/webserver.php' => config_path('webserver.php')], 'webserver-config');
        // adds views
        $this->loadViewsFrom(__DIR__ . '/../../views/webserver', 'webserver');
        // migrations
        $this->publishes([__DIR__.'/../../database/migrations/' => database_path('/migrations')], 'migrations');

        Website::observe(new Observers\WebsiteObserver());
        SslCertificate::observe(new Observers\SslCertificateObserver());

        /*
         * Ssl repository
         */
        $this->app->bind('Hyn\Webserver\Contracts\SslRepositoryContract', function ($app) {
            return new SslRepository(new SslCertificate(), new SslHostname());
        });

        /*
         * Toolbox command
         */
        $this->app->bind('hyn.webserver.command.toolbox', function ($app) {
            return new Commands\ToolboxCommand($app->make(WebsiteRepositoryContract::class));
        });

        $this->commands(['hyn.webserver.command.toolbox']);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'hyn.webserver.command.toolbox',
            'Hyn\Webserver\Contracts\SslRepositoryContract',
        ];
    }
}
