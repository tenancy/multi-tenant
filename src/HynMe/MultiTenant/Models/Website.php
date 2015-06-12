<?php namespace HynMe\MultiTenant\Models;

use HynMe\MultiTenant\Abstracts\Models\SystemModel;
use HynMe\MultiTenant\Tenant\DatabaseConnection;
use HynMe\MultiTenant\Tenant\Directory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracasts\Presenter\PresentableTrait;

class Website extends SystemModel
{
    use PresentableTrait,
        SoftDeletes;

    protected $presenter = 'HynMe\MultiTenant\Presenters\WebsitePresenter';

    protected $fillable = ['tenant_id', 'identifier'];

    protected $appends = ['directory'];

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function hostnames()
    {
        return $this->hasMany(Hostname::class)->with('certificate');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHostnamesWithCertificateAttribute()
    {
        return $this->hostnames()->whereNotNull('ssl_certificate_id')->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHostnamesWithoutCertificateAttribute()
    {
        return $this->hostnames()->whereNull('ssl_certificate_id')->get();
    }

    /**
     * Loads the unique id's from the certificates
     * @return array
     */
    public function getCertificateIdsAttribute()
    {
        return array_unique($this->hostnames()->whereNotNull('ssl_certificate_id')->lists('ssl_certificate_id'));
    }

    /**
     * Directory class
     * @return Directory
     */
    public function getDirectoryAttribute()
    {
        return new Directory($this);
    }

    /**
     * The website tenant
     * @return Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Database tenant connection handler
     * @return DatabaseConnection
     */
    public function getDatabaseAttribute()
    {
        return new DatabaseConnection($this);
    }
}