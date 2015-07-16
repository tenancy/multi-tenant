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
}