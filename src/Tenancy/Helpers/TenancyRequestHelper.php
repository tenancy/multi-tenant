<?php

namespace Hyn\Tenancy\Helpers;

use App;
use Hyn\Tenancy\Contracts\HostnameRepositoryContract;
use Illuminate\Database\QueryException;
use Request;

/**
 * Class TenancyRequestHelper.
 *
 * Helper class to identify requested hostname and website
 */
abstract class TenancyRequestHelper
{
    /**
     * Loads Hostname models based on request.
     *
     * @param HostnameRepositoryContract $hostname
     *
     * @return \Hyn\Tenancy\Models\Hostname
     */
    public static function hostname(HostnameRepositoryContract $hostname)
    {
        $tenant_hostname = null;

        try {
            if (! App::runningInConsole()) {
                $tenant_hostname = $hostname->findByHostname(Request::getHost());
            }

            elseif ($tenant_id = getenv('TENANT')) {
                $tenant_hostname = $hostname->findByWebsiteId($tenant_id);
            }

            if (! $tenant_hostname) {
                $tenant_hostname = $hostname->getDefault();
            }
        } catch (QueryException $e) {
            // table not found, set up not yet done
            if (preg_match('/\Qtable or view not found\E/', $e->getMessage())) {
                return;
            }
        }

        return $tenant_hostname;
    }
}
