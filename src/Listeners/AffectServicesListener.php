<?php

namespace Hyn\Tenancy\Listeners;

use Hyn\Tenancy\Abstracts\HostnameEvent;
use Hyn\Tenancy\Contracts\ServiceMutation;
use Hyn\Tenancy\Events\Hostnames\Identified;
use Illuminate\Contracts\Events\Dispatcher;

class AffectServicesListener
{
    public static $services = [];

    public function subscribe(Dispatcher $events)
    {
        $events->listen(Identified::class, [$this, 'activate']);
    }

    public static function registerService(ServiceMutation $service)
    {
        static::$services[get_class($service)] = $service;
    }

    protected function enable(HostnameEvent $event)
    {
        $this->processMethod('enable', $event->hostname);
    }

    protected function activate(HostnameEvent $event)
    {
        $this->processMethod('activate', $event->hostname);
    }

    protected function processMethod()
    {
        $args = func_get_args();

        $method = array_shift($args);

        collect(static::$services)->each(function ($service) use ($method, $args) {
            call_user_func_array(
                [$service, $method],
                $args
            );
        });
    }
}
