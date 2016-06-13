<?php

use Hyn\MultiTenant\Contracts\CustomerRepositoryContract;
use Hyn\MultiTenant\Contracts\DirectoryContract;
use Hyn\MultiTenant\Contracts\HostnameRepositoryContract;
use Hyn\MultiTenant\Contracts\TenantRepositoryContract;
use Hyn\MultiTenant\Contracts\WebsiteRepositoryContract;
use Hyn\MultiTenant\Models\Customer;
use Hyn\MultiTenant\Models\Hostname;
use Hyn\MultiTenant\Models\Tenant;
use Hyn\MultiTenant\Models\Website;
use Hyn\MultiTenant\Tenant\Directory;

if (! function_exists('tenant_path')) {
    /**
     * Loads a relative path in the tenant directory.
     *
     * @param string $path
     *
     * @return string|void
     */
    function tenant_path($path = '')
    {
        /** @var Directory $directory */
        $directory = app(DirectoryContract::class);

        if (! $directory) {
            return;
        }

        return sprintf('%s%s', $directory->base(), ltrim($path, '/'));
    }
}

if (!function_exists('website')) {
    /**
     * Loads a tenant website, or the current one.
     *
     * @param null $id
     *
     * @return Website|bool
     */
    function website($id = null)
    {
        if (!empty($id)) {
            return app(WebsiteRepositoryContract::class)->findById($id);
        }

        $hostname = app('tenant.hostname');

        return $hostname ? $hostname->website : false;
    }
}

if (!function_exists('customer')) {
    /**
     * Loads a customer, or the current one.
     *
     * @param null $id
     *
     * @return Customer|bool
     */
    function customer($id = null)
    {
        if (!empty($id)) {
            return app(CustomerRepositoryContract::class)->findById($id);
        }

        $hostname = app('tenant.hostname');

        return $hostname ? $hostname->customer : false;
    }
}

if (!function_exists('tenant')) {
    /**
     * Loads a tenant, or the current one.
     *
     * @param null $id
     *
     * @deprecated use customer() now.
     *
     * @return Tenant|bool
     */
    function tenant($id = null)
    {
        return customer($id);
    }
}

if (!function_exists('hostname')) {
    /**
     * Loads a hostname, or the current one.
     *
     * @param null $id
     *
     * @return Hostname|bool
     */
    function hostname($id = null)
    {
        if (!empty($id)) {
            return app(HostnameRepositoryContract::class)->findById($id);
        }

        $hostname = app('tenant.hostname');

        return $hostname ? $hostname : false;
    }
}
