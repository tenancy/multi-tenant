<?php namespace Laraflock\MultiTenant\Models;

use Laraflock\MultiTenant\Abstracts\Models\SystemModel;
use Laracasts\Presenter\PresentableTrait;

class Tenant extends SystemModel
{
    use PresentableTrait;

    /**
     * @var string
     */
    protected $presenter = 'Laraflock\MultiTenant\Presenters\TenantPresenter';

    protected $fillable = ['name', 'identifier'];

    public function hostnames()
    {
        return $this->hasMany(Hostname::class);
    }
    public function websites()
    {
        return $this->hasMany(Website::class);
    }

    public function reseller()
    {
        return $this->belongsTo(self::class);
    }
    public function referer()
    {
        return $this->belongsTo(self::class);
    }
    public function reselled()
    {
        return $this->hasMany(self::class, 'reseller_id');
    }
    public function refered()
    {
        return $this->hasMany(self::class, 'referer_id');
    }
}