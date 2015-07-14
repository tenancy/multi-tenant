<?php namespace HynMe\MultiTenant\Abstracts\Models;

use HynMe\Framework\Models\AbstractModel;

class TenantModel extends AbstractModel
{
    protected $connection = 'tenant';
}