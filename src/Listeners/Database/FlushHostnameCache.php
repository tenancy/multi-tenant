<?php

namespace Hyn\Tenancy\Listeners\Database;

use Hyn\Tenancy\Models\Hostname;
use Illuminate\Contracts\Cache\Factory;

class FlushHostnameCache
{
    /**
     * @var Factory
     */
    private $cache;

    public function __construct(Factory $cache)
    {
        $this->cache = $cache;
    }

    public function saved(Hostname $hostname)
    {
        if ($hostname->isDirty([
            'under_maintenance_since', 'website_id', 'force_https',
            'redirect_to', 'fqdn'
        ])) {
            $this->flush($hostname);
        }
    }

    protected function flush(Hostname $hostname)
    {
        $fqdn = $hostname->getOriginal('fqdn') ?? $hostname->fqdn;

        $this->cache->forget("tenancy.hostname.$fqdn");
    }
}
