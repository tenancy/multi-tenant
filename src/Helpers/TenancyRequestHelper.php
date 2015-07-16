<?php namespace LaraLeague\MultiTenant\Helpers;


use LaraLeague\MultiTenant\Contracts\HostnameRepositoryContract;
use Illuminate\Database\QueryException;
use Request, App;

/**
 * Class TenancyRequestHelper
 *
 * Helper class to identify requested hostname and website
 *
 * @package LaraLeague\MultiTenant\Helpers
 */
abstract class TenancyRequestHelper
{
    /**
     * Loads Hostname models based on request
     * @param HostnameRepositoryContract $hostname
     * @return \LaraLeague\MultiTenant\Models\Hostname
     */
    public static function hostname(HostnameRepositoryContract $hostname)
    {
        $tenant_hostname = null;

        try {
            if(!App::runningUnitTests() && !App::runningInConsole())
                $tenant_hostname = $hostname->findByHostname(Request::getHttpHost());

            if(!$tenant_hostname)
                $tenant_hostname = $hostname->getDefault();
        }
        catch(QueryException $e) {
            // table not found, set up not yet done
            if(preg_match('/\Qtable or view not found\E/', $e->getMessage()))
                return null;
        }

        return $tenant_hostname;
    }
}