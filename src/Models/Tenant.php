<?php namespace LaraLeague\MultiTenant\Models;

use LaraLeague\MultiTenant\Abstracts\Models\SystemModel;
use Laracasts\Presenter\PresentableTrait;

class Tenant extends SystemModel
{
    use PresentableTrait;

    /**
     * @var string
     */
    protected $presenter = 'LaraLeague\MultiTenant\Presenters\TenantPresenter';

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