<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/hyn/multi-tenant
 *
 */

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
    public function creating($model)
    {
        $this->fire(__FUNCTION__, $model);
    }

    /**
     * @param Model $model
     */
    public function created($model)
    {
        $this->fire(__FUNCTION__, $model);
    }

    /**
     * @param Model $model
     */
    public function updating($model)
    {
        $this->fire(__FUNCTION__, $model);
    }

    /**
     * @param Model $model
     */
    public function updated($model)
    {
        $this->fire(__FUNCTION__, $model);
    }

    /**
     * @param Model $model
     */
    public function deleting($model)
    {
        $this->fire(__FUNCTION__, $model);
    }

    /**
     * @param Model $model
     */
    public function deleted($model)
    {
        $this->fire(__FUNCTION__, $model);
    }

    /**
     * @param Model $model
     */
    public function saving($model)
    {
        $this->fire(__FUNCTION__, $model);
    }

    /**
     * @param Model $model
     */
    public function saved($model)
    {
        $this->fire(__FUNCTION__, $model);
    }

    /**
     * @param Model $model
     */
    public function restoring($model)
    {
        $this->fire(__FUNCTION__, $model);
    }

    /**
     * @param Model $model
     */
    public function restored($model)
    {
        $this->fire(__FUNCTION__, $model);
    }

    /**
     * @param string $event
     * @param Model $model
     */
    protected function fire(string $event, Model $model)
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
}
