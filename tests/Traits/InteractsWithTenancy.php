<?php

namespace Hyn\Tenancy\Tests\Traits;

use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;

trait InteractsWithTenancy
{
    /**
     * @var Hostname
     */
    protected $hostname;

    /**
     * @var Hostname
     */
    protected $tenant;

    /**
     * @var Website
     */
    protected $website;


    protected function loadHostnames()
    {
        $this->hostname = Hostname::where('fqdn', 'local.testing')->firstOrFail();
        $this->tenant = Hostname::where('fqdn', 'tenant.testing')->firstOrFail();
    }

    protected function setUpHostnames(bool $save = false)
    {
        Hostname::unguard();

        $hostname = new Hostname([
            'fqdn' => 'local.testing',
            'redirect_to' => null,
            'force_https' => false,
        ]);

        $this->hostname = $hostname;

        $tenant = new Hostname([
            'fqdn' => 'tenant.testing',
            'redirect_to' => null,
            'force_https' => false
        ]);

        $this->tenant = $tenant;

        Hostname::reguard();

        if ($save) {
            $this->hostname->save();
            $this->tenant->save();
        }
    }

    protected function loadWebsites()
    {
        $this->website = Website::firstOrFail();
    }

    protected function setUpWebsites(bool $save = false, bool $connect = false)
    {
        $this->website = new Website;

        if ($connect) {
            $this->website->hostnames()->save($this->hostname);
        }

        if ($save) {
            $this->website->save();
        }
    }

    protected function cleanupTenancy()
    {
        foreach (['website', 'hostname', 'tenant'] as $property) {
            if ($this->{$property} && $this->{$property}->exists) {
                $this->{$property}->delete();
            }
        }
    }
}
