<?php

namespace Hyn\Tenancy\Tests\Traits;

use Hyn\Tenancy\Events\Hostnames\Identified;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Traits\DispatchesEvents;

trait InteractsWithTenancy
{
    use DispatchesEvents;
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

    /**
     * @param bool $save
     * @param string|null $setActive
     */
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

    protected function activateTenant(string $tenant)
    {
        $hostname = $tenant == 'tenant' ? $this->tenant : $this->hostname;

        $this->emitEvent(
            new Identified($hostname)
        );
    }

    protected function loadWebsites()
    {
        $this->website = Website::firstOrFail();
    }

    /**
     * @param bool $save
     * @param bool $connect
     */
    protected function setUpWebsites(bool $save = false, bool $connect = false)
    {
        $this->website = new Website;

        if ($save) {
            $this->website->save();
        }

        if ($connect) {
            $this->website->hostnames()->save($this->hostname);
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
