<?php namespace HynMe\MultiTenant\Models;

use HynMe\MultiTenant\Abstracts\Models\SystemModel;

class Hostname extends SystemModel
{
    /**
     * @return \HynMe\MultiTenant\Models\Website
     */
    public function website()
    {
        return $this->belongsTo(__NAMESPACE__.'\Website');
    }
}