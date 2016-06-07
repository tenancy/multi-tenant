<?php

namespace Hyn\MultiTenant\Observers;

use Hyn\MultiTenant\Models\Hostname;

class HostnameObserver
{
    /**
     * @param \Hyn\MultiTenant\Models\Hostname $model
     */
    public function saved(Hostname $model)
    {
        // only trigger if the website already exists
        if ($model->website_id && $model->website && $model->website->exists) {
            $model->website->touch();
        }
    }

    public function deleted(Hostname $model)
    {
        if ($model->website_id) {
            $model->load('website');
            $model->website->touch();
        }
    }
}
