<?php

namespace Hyn\Tenancy\Jobs;

use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Events\Hostnames\Identified;
use Hyn\Tenancy\Models\Hostname;
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
    public function handle(Request $request, HostnameRepository $hostnameRepository): ?Hostname
    {
        $hostname = env('TENANCY_CURRENT_HOSTNAME');

        if (empty($hostname) && (app()->runningInConsole() || !$request->getHost())) {
            return $hostnameRepository->getDefault();
        }

        if (!$hostname) {
            $hostname = $request->getHost();
        }

        $hostname = $hostnameRepository->findByHostname($hostname);

        $this->emitEvent(new Identified($hostname));

        return $hostname;
    }
}
