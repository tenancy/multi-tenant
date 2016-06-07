<?php

namespace Hyn\MultiTenant\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware;

class HostnameMiddleware implements Middleware
{
    public function handle($request, Closure $next)
    {
        /* @var \Hyn\MultiTenant\Models\Hostname */
        $hostname = app('tenant.hostname');
        if ($hostname && ! is_null($redirect = $hostname->redirectActionRequired())) {
            return $redirect;
        }

        return $next($request);
    }
}
