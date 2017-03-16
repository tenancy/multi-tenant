<?php

namespace Hyn\Tenancy\Abstracts;

use Hyn\Tenancy\Traits\DispatchesEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use ReflectionClass;

abstract class ModelObserver
{
    use DispatchesEvents;

    /**
     * @param Model $model
     */
    public function created($model)
    {
        $this->fireStandardizedEvent(__FUNCTION__, $model);
    }

    /**
     * @param string $event
     * @param Model $model
     */
    protected function fireStandardizedEvent(string $event, Model $model)
    {
        $eventClass = sprintf(
            'Hyn\\Tenancy\\Events\\%s\\%s',
            Str::plural((new ReflectionClass($model))->getShortName()),
            Str::camel($event)
        );

        if (class_exists($eventClass)) {
            $this->emitEvent(new $eventClass($model));
        }
    }

    /**
     * @param Model $model
     */
    public function updated($model)
    {
        $this->fireStandardizedEvent(__FUNCTION__, $model);
    }

    /**
     * @param Model $model
     */
    public function deleted($model)
    {
        $this->fireStandardizedEvent(__FUNCTION__, $model);
    }
}
