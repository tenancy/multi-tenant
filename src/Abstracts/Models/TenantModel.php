<?php namespace LaraLeague\MultiTenant\Abstracts\Models;

use HynMe\Framework\Models\AbstractModel;

class TenantModel extends AbstractModel
{
    protected $connection = 'tenant';
}