<?php namespace LaraLeague\MultiTenant\Observers;

class TenantObserver
{
    public function deleting($model)
    {
        foreach($model->hostnames as $hostname)
            $hostname->delete();
        foreach($model->websites as $website)
            $website->delete();
    }
}