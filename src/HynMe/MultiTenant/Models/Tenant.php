<?php namespace HynMe\MultiTenant\Models;

use HynMe\MultiTenant\Abstracts\Models\SystemModel;
use Laracasts\Presenter\PresentableTrait;

class Tenant extends SystemModel
{
    use PresentableTrait;

    /**
     * @var string
     */
    protected $presenter = 'HynMe\MultiTenant\Presenters\TenantPresenter';

    public function hostnames()
    {
        return $this->hasMany(__NAMESPACE__.'\Hostname');
    }
    public function websites()
    {
        return $this->hasMany(__NAMESPACE__.'\Website');
    }
}