<?php

namespace Hyn\Tenancy\Jobs;

use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Models\Hostname;
use Illuminate\Http\Request;

class HostnameIdentification
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var HostnameRepository
     */
    protected $hostnameRepository;

    public function __construct(Request $request, HostnameRepository $hostnameRepository)
    {
        $this->request = $request;
        $this->hostnameRepository = $hostnameRepository;
    }

    /**
     * @return Hostname|null
     */
    public function handle() : ?Hostname
    {
        $hostname = env('TENANCY_CURRENT_HOSTNAME');

        if (!$hostname && (app()->runningInConsole() || !$this->request->getHost())) {
            return $this->hostnameRepository->getDefault();
        }

        if (!$hostname) {
            $hostname = $this->request->getHost();
        }

        return $this->hostnameRepository->findByHostname($hostname);
    }
}
