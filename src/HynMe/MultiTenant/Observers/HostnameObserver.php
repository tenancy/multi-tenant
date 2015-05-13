<?php namespace HynMe\MultiTenant\Observers;

class HostnameObserver
{
    /**
     * @param \HynMe\MultiTenant\Models\Hostname $model
     */
    public function saving($model)
    {
        if($model->website && $model->isDirty('website_id'))
            $model->website->touch();
    }
}