<?php namespace HynMe\MultiTenant\Models;

use HynMe\MultiTenant\Abstracts\Models\SystemModel;
use HynMe\MultiTenant\Tenant\Directory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracasts\Presenter\PresentableTrait;

class Website extends SystemModel
{
    use PresentableTrait,
        SoftDeletes;

    protected $presenter = 'HynMe\MultiTenant\Presenters\WebsitePresenter';

    protected $fillable = ['tenant_id', 'identifier'];

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function hostnames()
    {
        return $this->hasMany(__NAMESPACE__.'\Hostname');
    }

    /**
     * Directory class
     * @return Directory
     */
    public function getDirectoryAttribute()
    {
        return new Directory($this);
    }
}