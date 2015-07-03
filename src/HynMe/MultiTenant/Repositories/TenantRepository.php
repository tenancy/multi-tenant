<?php namespace HynMe\MultiTenant\Repositories;

use HynMe\Framework\Repositories\BaseRepository;
use HynMe\MultiTenant\Contracts\TenantRepositoryContract;

class TenantRepository extends BaseRepository implements TenantRepositoryContract
{

    /**
     * @var \HynMe\MultiTenant\Models\Tenant
     */
    protected $tenant;


    /**
     * Find a tenant by name
     * @param $name
     * @return \HynMe\MultiTenant\Models\Tenant
     */
    public function findByName($name)
    {
        return $this->tenant->where('name', $name)->first();
    }

    /**
     * Removes tenant and everything related
     *
     * @param $name
     * @return bool|null
     */
    public function forceDeleteByName($name)
    {
        $tenant = $this->tenant->where('name', $name)->first();
        return $tenant ? $tenant->delete() : null;
    }
}