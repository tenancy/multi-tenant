<?php

namespace Hyn\MultiTenant\Models;

use Carbon\Carbon;
use Hyn\MultiTenant\Abstracts\Models\SystemModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracasts\Presenter\PresentableTrait;

/**
 * @property string     $name
 * @property string     $email
 * @property string     $customer_no
 * @property boolean    $administrator
 * @property integer    $reseller_id
 * @property integer    $referer_id
 * @property Tenant     $referer
 * @property Tenant     $reseller
 * @property Collection $refered
 * @property Collection $reselled
 * @property Collection $hostnames
 * @property Collection $websites
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 * @property Carbon     $deleted_at
 */
class Tenant extends SystemModel
{
    use PresentableTrait, SoftDeletes;

    /**
     * @var string
     */
    protected $presenter = 'Hyn\MultiTenant\Presenters\TenantPresenter';

    protected $fillable = ['name', 'email', 'customer_no'];

    /**
     * All hostnames of this tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hostnames()
    {
        return $this->hasMany(Hostname::class);
    }

    /**
     * All websites of this tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function websites()
    {
        return $this->hasMany(Website::class);
    }

    /**
     * The reseller of this tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reseller()
    {
        return $this->belongsTo(self::class);
    }

    /**
     * The referer of this tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referer()
    {
        return $this->belongsTo(self::class);
    }

    /**
     * Those who have been reselled by this tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reselled()
    {
        return $this->hasMany(self::class, 'reseller_id');
    }

    /**
     * Those that have been refered by this tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function refered()
    {
        return $this->hasMany(self::class, 'referer_id');
    }
}
