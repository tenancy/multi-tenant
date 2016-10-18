<?php

namespace Hyn\Tenancy\Models;

use Carbon\Carbon;
use Hyn\Tenancy\Abstracts\SystemModel;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property Website[] $websites
 * @property Hostname[] $hostnames
 */
class Customer extends SystemModel
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function websites()
    {
        return $this->hasMany(Website::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hostnames()
    {
        return $this->hasMany(Hostname::class);
    }
}
