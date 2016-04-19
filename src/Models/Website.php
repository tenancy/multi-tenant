<?php

namespace Hyn\MultiTenant\Models;

use Carbon\Carbon;
use Hyn\MultiTenant\Abstracts\Models\SystemModel;
use Hyn\MultiTenant\Tenant\DatabaseConnection;
use Hyn\MultiTenant\Tenant\Directory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracasts\Presenter\PresentableTrait;

/**
 * @property string             $identifier
 * @property int                $tenant_id
 * @property Directory          $directory
 * @property DatabaseConnection $database
 * @property Collection         $hostnames
 * @property Tenant             $tenant
 * @property string             $websiteUser
 * @property Collection         $hostnamesWithCertificate
 * @property Collection         $hostnamesWithoutCertificate
 * @property array              $certificateIds
 * @property Carbon             $created_at
 * @property Carbon             $updated_at
 * @property Carbon             $deleted_at
 */
class Website extends SystemModel
{
    use PresentableTrait,
        SoftDeletes;

    protected $presenter = 'Hyn\MultiTenant\Presenters\WebsitePresenter';

    protected $fillable = ['tenant_id', 'identifier'];

    protected $appends = ['directory'];

    /**
     * Load all hostnames that have a certificate.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHostnamesWithCertificateAttribute()
    {
        return $this->hostnames()->whereNotNull('ssl_certificate_id')->get();
    }

    /**
     * Loads all hostnames of this website.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function hostnames()
    {
        return $this->hasMany(Hostname::class)->with('certificate');
    }

    /**
     * Loads all hostnames that have no certificate.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHostnamesWithoutCertificateAttribute()
    {
        return $this->hostnames()->whereNull('ssl_certificate_id')->get();
    }

    /**
     * Loads the unique id's from the certificates.
     *
     * @return array
     */
    public function getCertificateIdsAttribute()
    {
        return array_unique($this->hostnames()->whereNotNull('ssl_certificate_id')->lists('ssl_certificate_id'));
    }

    /**
     * Directory class.
     *
     * @return Directory
     */
    public function getDirectoryAttribute()
    {
        return new Directory($this);
    }

    /**
     * The website tenant.
     *
     * @return Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Database tenant connection handler.
     *
     * @return DatabaseConnection
     */
    public function getDatabaseAttribute()
    {
        return new DatabaseConnection($this);
    }

    /**
     * Loads the user the website should be run as.
     *
     * @return string
     */
    public function getWebsiteUserAttribute()
    {
        if (config('webserver.default-user') === true) {
            return $this->identifier;
        }

        return config('webserver.default-user');
    }
}
