<?php

namespace Laraflock\MultiTenant\Contracts;

use Hyn\Framework\Contracts\BaseRepositoryContract;

interface TenantRepositoryContract extends BaseRepositoryContract
{
    /**
     * Load all tenants.
     *
     * @return mixed
     */
    public function all();

    /**
     * Removes tenant and everything related.
     *
     * @param $name
     *
     * @return bool|null
     */
    public function forceDeleteByName($name);

    /**
     * Find a tenant by name.
     *
     * @param $name
     *
     * @return \Laraflock\MultiTenant\Models\Tenant
     */
    public function findByName($name);
}
