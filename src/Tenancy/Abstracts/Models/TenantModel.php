<?php

namespace Hyn\Tenancy\Abstracts\Models;

use Hyn\Framework\Models\AbstractModel;
use Hyn\Tenancy\Tenant\DatabaseConnection;

class TenantModel extends AbstractModel
{
    public function getConnectionName()
    {
        return DatabaseConnection::tenantConnectionName();
    }
}
