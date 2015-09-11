<?php

namespace Laraflock\MultiTenant\Observers;

class HostnameObserver
{
    /**
     * @param \Laraflock\MultiTenant\Models\Hostname $model
     */
    public function saved($model)
    {
        if ($model->website_id) {
            $model->load('website');
            $model->website->touch();
        }
    }

    public function deleted($model)
    {
        if ($model->website_id) {
            $model->load('website');
            $model->website->touch();
        }
    }
}
