<?php

namespace Hyn\MultiTenant\Observers;

use Hyn\MultiTenant\Models\Customer;

class CustomerObserver
{
    public function deleting(Customer $model)
    {
        foreach ($model->hostnames as $hostname) {
            $hostname->delete();
        }
        foreach ($model->websites as $website) {
            $website->delete();
        }
    }
}
