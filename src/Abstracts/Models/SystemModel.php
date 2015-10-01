<?php

namespace Laraflock\MultiTenant\Abstracts\Models;

use Hyn\Framework\Models\AbstractModel;
use Laraflock\MultiTenant\Tenant\DatabaseConnection;

class SystemModel extends AbstractModel
{
    public function getConnectionName()
    {
        return DatabaseConnection::systemConnectionName();
    }
}
