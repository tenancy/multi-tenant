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

namespace Hyn\Tenancy\Jobs;

use Hyn\Tenancy\Contracts\Hostname;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Events;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Illuminate\Http\Request;

class HostnameIdentification
{
    use DispatchesEvents;

    /**
     * @param Request $request
     * @param HostnameRepository $hostnameRepository
     * @return Hostname|null
     */
    public function handle(Request $request, HostnameRepository $hostnameRepository)
    {
        $hostname = env('TENANCY_CURRENT_HOSTNAME');

        if (!$hostname && $request->getHost()) {
            $hostname = $request->getHost();
        }

        if ($hostname) {
            $hostname = $hostnameRepository->findByHostname($hostname);
        }

        if (!$hostname) {
            $hostname = $hostnameRepository->getDefault();
        }

        $this->emitEvent(new Events\Hostnames\Identified($hostname));

        if (optional($hostname)->website) {
            $this->emitEvent(new Events\Websites\Identified($hostname->website, $hostname));
        } else {
            $this->emitEvent(new Events\Websites\NoneFound($request));
        }

        return $hostname;
    }
}
