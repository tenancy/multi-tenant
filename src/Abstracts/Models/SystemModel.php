<?php

namespace Hyn\MultiTenant\Abstracts\Models;

use Hyn\Framework\Models\AbstractModel;
use Hyn\MultiTenant\Tenant\DatabaseConnection;

class SystemModel extends AbstractModel
{
    public function getConnectionName()
    {
        return DatabaseConnection::systemConnectionName();
    }
}
