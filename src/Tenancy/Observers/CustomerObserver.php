<?php

namespace Hyn\Tenancy\Observers;

use Hyn\Tenancy\Models\Customer;

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
