<?php namespace HynMe\MultiTenant\Models;

use HynMe\MultiTenant\Abstracts\Models\SystemModel;

class Tenant extends SystemModel
{
    public function hostnames()
    {
        return $this->hasMany(__NAMESPACE__.'\Hostname');
    }
    public function websites()
    {
        return $this->hasMany(__NAMESPACE__.'\Website');
    }
}