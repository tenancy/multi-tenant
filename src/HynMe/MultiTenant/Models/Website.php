<?php namespace HynMe\MultiTenant\Models;

use HynMe\MultiTenant\Abstracts\Models\SystemModel;
use Laracasts\Presenter\PresentableTrait;

class Website extends SystemModel
{
    use PresentableTrait;

    protected $fillable = ['tenant_id', 'identifier'];

    protected $presenter = 'HynMe\MultiTenant\Presenters\WebsitePresenter';

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function hostnames()
    {
        return $this->hasMany(__NAMESPACE__.'\Hostname');
    }
}