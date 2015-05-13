<?php namespace HynMe\MultiTenant\Observers;

class HostnameObserver
{
    /**
     * @param \HynMe\MultiTenant\Models\Hostname $model
     */
    public function saved($model)
    {
    }

    public function deleted($model)
    {
    }
}