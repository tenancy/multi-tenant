<?php

namespace Hyn\Tenancy\Listeners;

use Hyn\Tenancy\Traits\DispatchesEvents;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ModelListener
{
    use DispatchesEvents;

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        foreach ([
                     'creating',
                     'created',
                     'updating',
                     'updated',
                     'deleting',
                     'deleted',
                     'saving',
                     'saved',
                     'restoring',
                     'restored',
                 ] as $event) {
            $events->listen("eloquent.{$event}", [$this, 'dispatch']);
        }
    }

    /**
     * @param string $event
     * @param Model  $model
     */
    protected function dispatch(string $event, Model $model)
    {
        list($_, $postfix) = explode(".", $event);
        $eventClass = sprintf(
            'Hyn\\Tenancy\\Events\\%s\\%s',
            Str::plural((new \ReflectionClass($model))->getShortName()),
            Str::camel($postfix)
        );

        if ($eventClass) {
            $this->emitEvent(new $eventClass($model));
        }
    }
}
