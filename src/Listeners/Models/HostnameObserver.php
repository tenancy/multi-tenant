<?php

namespace Hyn\Tenancy\Listeners\Models;

use Hyn\Tenancy\Abstracts\ModelObserver;
use Hyn\Tenancy\Events\Hostnames\Attached;
use Hyn\Tenancy\Events\Hostnames\Detached;
use Hyn\Tenancy\Models\Hostname;

class HostnameObserver extends ModelObserver
{
    /**
     * @param Hostname $model
     */
    public function updated($model)
    {
        parent::updated($model);

        if ($model->isDirty('website_id') && $model->website_id) {
            $this->emitEvent(new Attached($model));
        }

        if ($model->isDirty('website_id') && !$model->website_id) {
            $this->emitEvent(new Detached($model));
        }
    }
}
