<?php

namespace Hyn\Tenancy\Listeners\Filesystem;

use Hyn\Tenancy\Abstracts\WebsiteEvent;
use Hyn\Tenancy\Events\Websites\Identified;
use Hyn\Tenancy\Events\Websites\Switched;
use Hyn\Tenancy\Services\ConfigurationLoader;
use Illuminate\Contracts\Events\Dispatcher;

class ResetConfigs
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen([Identified::class, Switched::class], [$this, 'reset']);
    }

    public function reset(WebsiteEvent $event){
        if($event->website)
        {
            config(app()->call(ConfigurationLoader::class . '@reset'));
        }
    }
}
