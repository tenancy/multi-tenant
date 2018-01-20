<?php
namespace Hyn\Tenancy\Providers\Tenants;

use Hyn\Tenancy\Contracts\CurrentHostname;
use Hyn\Tenancy\Jobs\HostnameIdentification;
use Hyn\Tenancy\Traits\DispatchesJobs;
use Illuminate\Support\ServiceProvider;

class HostnameProvider extends ServiceProvider
{
    use DispatchesJobs;

    public function provides()
    {
        return [CurrentHostname::class];
    }

    public function register()
    {
        $this->app->singleton(CurrentHostname::class, function () {
            $hostname = $this->dispatch(new HostnameIdentification);

            return $hostname;
        });
    }
}