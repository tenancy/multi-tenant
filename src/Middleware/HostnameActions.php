<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Middleware;

use Closure;
use Hyn\Tenancy\Contracts\Hostname;
use Hyn\Tenancy\Events\Hostnames\NoneFound;
use Hyn\Tenancy\Events\Hostnames\Redirected;
use Hyn\Tenancy\Events\Hostnames\Secured;
use Hyn\Tenancy\Events\Hostnames\UnderMaintenance;
use Hyn\Tenancy\Contracts\Hostname;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class HostnameActions
{
    use DispatchesEvents;
    /**
     * @var Hostname|Hostname
     */
    protected $hostname;

    /**
     * @var Redirector
     */
    protected $redirect;

    /**
     * @param Hostname $hostname
     * @param Redirector $redirect
     */
    public function __construct(Hostname $hostname = null, Redirector $redirect)
    {
        $this->hostname = $hostname;
        $this->redirect = $redirect;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->hostname != null) {
            if ($this->hostname->under_maintenance_since) {
                return $this->maintenance($this->hostname);
            }

            if ($this->hostname->redirect_to) {
                return $this->redirect($this->hostname);
            }

            if (!$request->secure() && $this->hostname->force_https) {
                return $this->secure($this->hostname, $request);
            }
        } elseif ($response = $this->abort($request)) {
            return $response;
        }

        return $next($request);
    }

    /**
     * @param Hostname $hostname
     * @return RedirectResponse
     */
    protected function redirect(Hostname $hostname)
    {
        $this->emitEvent(new Redirected($hostname));

        return $this->redirect->away($hostname->redirect_to);
    }

    /**
     * @param Hostname $hostname
     * @param Request $request
     * @return RedirectResponse
     */
    protected function secure(Hostname $hostname, Request $request)
    {
        $this->emitEvent(new Secured($hostname));

        return $this->redirect->secure($request->path());
    }

    /**
     * @param Hostname $hostname
     */
    protected function maintenance(Hostname $hostname)
    {
        $this->emitEvent(new UnderMaintenance($hostname));
        throw new MaintenanceModeException($hostname->under_maintenance_since->timestamp);
    }

    /**
     * Aborts the application.
     * @param Request $request
     */
    protected function abort(Request $request)
    {
        if (config('tenancy.hostname.abort-without-identified-hostname')) {
            $this->emitEvent(new NoneFound($request));
            return abort(404);
        }
    }
}
