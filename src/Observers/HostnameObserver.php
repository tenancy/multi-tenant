<?php

namespace Hyn\MultiTenant\Observers;

class HostnameObserver
{
    /**
     * @param \Hyn\MultiTenant\Models\Hostname $model
     */
    public function saved($model)
    {
        // only trigger if the website already exists
        if ($model->website_id && $model->website && $model->website->exists) {
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
