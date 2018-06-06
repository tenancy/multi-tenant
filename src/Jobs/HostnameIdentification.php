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

namespace Hyn\Tenancy\Jobs;

use Hyn\Tenancy\Contracts\Hostname;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Events\Hostnames\Identified;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Illuminate\Http\Request;

class HostnameIdentification
{
    use DispatchesEvents;

    /**
     * @param Request            $request
     * @param HostnameRepository $hostnameRepository
     * @return Hostname|null
     */
    public function handle(Request $request, HostnameRepository $hostnameRepository)
    {
        $hostname       = env('TENANCY_CURRENT_HOSTNAME');
        $check_base_url = env('TENANCY_SKIP_IF_EQUAL_TO_BASE_URL');

        if (!$hostname && $request->getHost()) {
            $hostname = $request->getHost();
        }

        if (
            $check_base_url
            && ($default_name = config('url_base'))
            && $default_name === $hostname
        ) {
            $this->emitEvent(new Identified());
            return null;
        }

        if ($hostname) {
            $hostname = $hostnameRepository->findByHostname($hostname);
        }

        if (!$hostname) {
            $hostname = $hostnameRepository->getDefault();
        }

        $this->emitEvent(new Identified($hostname));

        return $hostname;
    }
}
