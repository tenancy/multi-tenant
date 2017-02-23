<?php

namespace Hyn\Tenancy\Listeners;

use Hyn\Tenancy\Abstracts\HostnameEvent;
use Hyn\Tenancy\Contracts\ServiceMutation;
use Hyn\Tenancy\Events\Hostnames\Identified;
use Illuminate\Contracts\Events\Dispatcher;

class AffectServicesListener
{
    /**
     * Registered services.
     *
     * @var array
     */
    public static $services = [];

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Identified::class, [$this, 'activate']);
    }

    /**
     * @param ServiceMutation $service
     */
    public static function registerService(ServiceMutation $service)
    {
        static::$services[get_class($service)] = $service;
    }

    /**
     * @param HostnameEvent $event
     */
    protected function enable(HostnameEvent $event)
    {
        $this->processMethod('enable', $event->hostname);
    }

    /**
     * @param HostnameEvent $event
     */
    protected function activate(HostnameEvent $event)
    {
        $this->processMethod('activate', $event->hostname);
    }

    /**
     * Processes an event by forwarding them to the listeners.
     */
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
