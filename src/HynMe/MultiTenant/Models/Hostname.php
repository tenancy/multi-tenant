<?php namespace HynMe\MultiTenant\Models;

use HynMe\MultiTenant\Abstracts\Models\SystemModel;
use Laracasts\Presenter\PresentableTrait;

class Hostname extends SystemModel
{
    use PresentableTrait;

    /**
     * @var string
     */
    protected $presenter = 'HynMe\MultiTenant\Presenters\HostnamePresenter';

    /**
     * @var array
     */
    protected $fillable = ['website_id', 'hostname'];

    /**
     * @return \HynMe\MultiTenant\Models\Website
     */
    public function website()
    {
        return $this->belongsTo(__NAMESPACE__.'\Website');
    }
}