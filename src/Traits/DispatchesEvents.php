<?php

namespace Hyn\Tenancy\Traits;

use Hyn\Tenancy\Abstracts\AbstractEvent;
use Illuminate\Contracts\Events\Dispatcher;

trait DispatchesEvents
{
    /**
     * @param AbstractEvent $event
     * @param array $payload
     * @return array|null
     */
    public function emitEvent(AbstractEvent $event, array $payload = [])
    {
        return app(Dispatcher::class)->fire($event, $payload);
    }
}