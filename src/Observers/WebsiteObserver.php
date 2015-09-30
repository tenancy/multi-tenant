<?php

namespace Laraflock\MultiTenant\Observers;

use Illuminate\Foundation\Bus\DispatchesJobs;

class WebsiteObserver
{
    use DispatchesJobs;

    /**
     * @param $model
     *
     * @return bool
     */
    public function updating($model)
    {
        if ($model->isDirty('identifier')) {
            /*
             * Move tenant directories once the identifier changes
             */
            return $model->directory->move();
        }
    }

    public function creating($model)
    {
    }

    /**
     * @param $model
     */
    public function created($model)
    {
        $model->directory->create();
    }

    public function deleted($model)
    {
        $model->directory->delete();
    }
}
