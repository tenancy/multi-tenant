<?php

namespace Hyn\Tenancy\Observers;

use Hyn\Tenancy\Models\Website;
use Illuminate\Foundation\Bus\DispatchesJobs;

class WebsiteObserver
{
    use DispatchesJobs;

    /**
     * @param $model
     *
     * @return bool
     */
    public function updating(Website $model)
    {
        if ($model->isDirty('identifier')) {
            /*
             * Move tenant directories once the identifier changes
             */
            return $model->directory->move();
        }
    }

    /**
     * @param $model
     */
    public function created(Website $model)
    {
        $model->directory->create();
    }

    public function deleted(Website $model)
    {
        $model->directory->delete();
    }
}
