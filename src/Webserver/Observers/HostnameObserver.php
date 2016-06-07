<?php

namespace Hyn\Webserver\Observers;

use Hyn\Webserver\Commands\LetsEncryptCommand;
use Illuminate\Foundation\Bus\DispatchesJobs;

class HostnameObserver
{
    use DispatchesJobs;

    /**
     * @param $model
     */
    public function saved($model)
    {
        if (! $model->certificate || $model->certificate->isExpired === true) {
            $this->dispatch(
                new LetsEncryptCommand($model->id)
            );
        }
    }
}
