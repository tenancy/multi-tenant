<?php

namespace Hyn\Webserver\Observers;

use Hyn\Webserver\Commands\WebserverCommand;
use Illuminate\Foundation\Bus\DispatchesJobs;

class WebsiteObserver
{
    use DispatchesJobs;

    public function created($model)
    {
        $this->dispatch(
            new WebserverCommand($model->id, 'create')
        );
    }

    public function updated($model)
    {
        $this->dispatch(
            new WebserverCommand($model->id, 'update')
        );
    }

    public function deleting($model)
    {
        $this->dispatch(
            new WebserverCommand($model->id, 'delete')
        );
    }
}
