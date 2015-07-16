<?php namespace LaraLeague\MultiTenant\Observers;

class HostnameObserver
{
    /**
     * @param \LaraLeague\MultiTenant\Models\Hostname $model
     */
    public function saved($model)
    {
        if($model->website)
            $model->website->touch();
    }

    public function deleted($model)
    {
        if($model->website)
            $model->website->touch();
    }
}