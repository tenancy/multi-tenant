<?php

namespace Laraflock\MultiTenant\Abstracts\Models;

use HynMe\Framework\Models\AbstractModel;

class TenantModel extends AbstractModel
{
    protected $connection;

    public function __construct(array $attributes = [])
    {
        $this->connection = env('TENANT_CONNECTION', 'tenant');
        parent::__construct($attributes);
    }
}
