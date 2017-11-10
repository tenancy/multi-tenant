<?php

namespace Hyn\Tenancy\Middleware;

use Closure;
use Hyn\Tenancy\Environment;
use Illuminate\Http\Request;

class EagerIdentification
{
    public function handle(Request $request, Closure $next)
    {
        if (config('tenancy.hostname.early-identification')) {
            app(Environment::class);
        }

        return $next($request);
    }
}
