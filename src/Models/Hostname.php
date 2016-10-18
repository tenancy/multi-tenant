<?php

namespace Hyn\Tenancy\Models;

use Carbon\Carbon;
use Hyn\Tenancy\Abstracts\SystemModel;

/**
 * @property int $id
 * @property string $fqdn
 * @property string $redirect_to
 * @property bool $force_https
 * @property Carbon $under_maintenance_since
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property int $website_id
 * @property Website $website
 * @property int $customer_id
 * @property Customer $customer
 */
class Hostname extends SystemModel
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
