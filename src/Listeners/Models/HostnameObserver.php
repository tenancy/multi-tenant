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
