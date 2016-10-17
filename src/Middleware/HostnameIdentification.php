<?php

namespace Hyn\Tenancy\Middleware;

use Closure;
use Hyn\Tenancy\Contracts\CurrentHostname;
use Hyn\Tenancy\Models\Hostname;

class HostnameMiddleware
{
    /**
     * @var CurrentHostname|Hostname
     */
    protected $hostname;

    public function __construct(CurrentHostname $hostname)
    {
        $this->hostname = $hostname;
    }

    public function handle($request, Closure $next)
    {

        if ($this->hostname) {
            // todo
        }

        return $next($request);
    }
}