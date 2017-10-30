<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Models;

use Carbon\Carbon;
use Hyn\Tenancy\Abstracts\SystemModel;
use Hyn\Tenancy\Contracts\Website as WebsiteContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $uuid
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property int $customer_id
 * @property Customer $customer
 * @property Hostname[] $hostnames
 */
class Website extends SystemModel implements WebsiteContract
{
    public function customer(): BelongsTo
    {
        return $this->belongsTo(config('tenancy.models.customer'));
    }

    public function hostnames(): HasMany
    {
        return $this->hasMany(config('tenancy.models.hostname'));
    }
}
