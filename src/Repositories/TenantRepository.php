<?php

namespace Laraflock\MultiTenant\Repositories;

use Hyn\Framework\Repositories\BaseRepository;
use Laraflock\MultiTenant\Contracts\TenantRepositoryContract;

class TenantRepository extends BaseRepository implements TenantRepositoryContract
{
    /**
     * @var \Laraflock\MultiTenant\Models\Tenant
     */
    protected $tenant;

    /**
     * Find a tenant by name.
     *
     * @param $name
     *
     * @return \Laraflock\MultiTenant\Models\Tenant
     */
    public function findByName($name)
    {
        return $this->tenant->where('name', $name)->first();
    }

    /**
     * Removes tenant and everything related.
     *
     * @param $name
     *
     * @return bool|null
     */
    public function forceDeleteByName($name)
    {
        $tenant = $this->tenant->where('name', $name)->first();

        return $tenant ? $tenant->delete() : null;
    }
}
