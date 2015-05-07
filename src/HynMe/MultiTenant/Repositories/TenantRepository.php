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
     * Create a pagination object
     *
     * @param int $per_page
     * @return mixed
     */
    public function paginated($per_page = 20)
    {
        return $this->tenant->paginate($per_page);
    }

    public function all()
    {
        return $this->tenant->all();
    }
}