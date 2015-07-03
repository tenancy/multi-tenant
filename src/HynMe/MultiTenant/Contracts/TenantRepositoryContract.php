<?php namespace HynMe\MultiTenant\Contracts;

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
}