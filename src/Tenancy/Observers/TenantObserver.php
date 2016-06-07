<?php

namespace Hyn\MultiTenant\Observers;

use Hyn\MultiTenant\Models\Tenant;

class TenantObserver
{
    public function deleting(Tenant $model)
    {
        foreach ($model->hostnames as $hostname) {
            $hostname->delete();
        }
        foreach ($model->websites as $website) {
            $website->delete();
        }
    }
}
