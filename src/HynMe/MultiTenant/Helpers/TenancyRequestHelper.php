<?php namespace HynMe\MultiTenant\Helpers;


use HynMe\MultiTenant\Contracts\HostnameRepositoryContract;
use HynMe\MultiTenant\Contracts\WebsiteRepositoryContract;
use Request;

/**
 * Class TenancyRequestHelper
 *
 * Helper class to identify requested hostname and website
 *
 * @package HynMe\MultiTenant\Helpers
 */
abstract class TenancyRequestHelper
{
    /**
     * Loads Hostname models based on request
     * @param HostnameRepositoryContract $hostname
     * @return \HynMe\MultiTenant\Models\Hostname
     */
    public static function hostname(HostnameRepositoryContract $hostname)
    {
        $hostname = ($host = $hostname->findByHostname(Request::getHttpHost()))
            ? $host
            : $hostname->getDefault();

        return $hostname;
    }
}