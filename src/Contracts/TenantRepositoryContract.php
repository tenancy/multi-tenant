<?php namespace LaraLeague\MultiTenant\Contracts;

use HynMe\Framework\Contracts\BaseRepositoryContract;

interface TenantRepositoryContract extends BaseRepositoryContract
{
    /**
     * Load all tenants
     * @return mixed
     */
    public function all();

    /**
     * Removes tenant and everything related
     * @param $name
     * @return bool|null
     */
    public function forceDeleteByName($name);

    /**
     * Find a tenant by name
     * @param $name
     * @return \LaraLeague\MultiTenant\Models\Tenant
     */
    public function findByName($name);
}