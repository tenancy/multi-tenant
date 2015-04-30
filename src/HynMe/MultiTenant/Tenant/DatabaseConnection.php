<?php namespace HynMe\MultiTenant\Tenant;

use Config;
use HynMe\MultiTenant\Models\Hostname;

/**
 * Class DatabaseConnection
 *
 * Helps with tenant database connections
 * @package HynMe\MultiTenant\Tenant
 */
class DatabaseConnection
{
    /**
     * Sets the tenant database connection
     * @param Hostname $hostname
     */
    public static function setup(Hostname $hostname)
    {
        $clone = Config::get('database.connections.system');
        $clone['password'] = md5(env('APP_KEY') . $hostname->hostname);
        $clone['username'] = $clone['database'] = str_replace(['.'], '-', $hostname->hostname);
        Config::set('database.connections.tenant', $clone);
    }
}