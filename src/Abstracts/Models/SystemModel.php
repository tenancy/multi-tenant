<?php

namespace Laraflock\MultiTenant\Abstracts\Models;

use HynMe\Framework\Models\AbstractModel;

class SystemModel extends AbstractModel
{
    protected $connection = 'hyn';
}
