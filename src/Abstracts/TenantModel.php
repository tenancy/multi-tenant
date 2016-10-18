<?php

namespace Hyn\Tenancy\Abstracts;

use Hyn\Tenancy\Database\Connection;

abstract class TenantModel extends AbstractModel
{
    public function getConnectionName()
    {
        return app(Connection::class)->tenantName();
    }
}
