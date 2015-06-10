<?php namespace HynMe\MultiTenant\Models;

use HynMe\MultiTenant\Abstracts\Models\SystemModel;
use Laracasts\Presenter\PresentableTrait;
use Request;

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
    protected $fillable = ['website_id','hostname','redirect_to','prefer_https','sub_of'];

    protected $appends = [];

    /**
     * @return null|Teanant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return null|Website
     */
    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Host to redirect to
     * @return null|Hostname
     */
    public function redirectToHostname()
    {
        return $this->belongsTo(static::class, 'redirect_to');
    }

    /**
     * Host this is a sub domain of
     * @return null|Hostname
     */
    public function subDomainOf()
    {
        return $this->belongsTo(static::class, 'sub_of');
    }

    /**
     * Sub domains of this hostname
     * @return \Illuminate\Eloquent\Collection
     */
    public function subDomains()
    {
        return $this->hasMany(static::class, 'sub_of');
    }

    /**
     * @return \HynMe\Webserver\Models\SslCertificate|null
     */
    public function certificate()
    {
        return $this->belongsTo('HynMe\Webserver\Models\SslCertificate', 'ssl_certificate_id');
    }

    /**
     * Identifies whether a redirect is required for this hostname
     * @return \Illuminate\Http\RedirectResponse|null
     */
    public function redirectActionRequired()
    {
        // force to new hostname
        if($this->redirect_to)
            return $this->redirectToHostname->redirectActionRequired();
        // @todo also add ssl check once ssl certificates are support
        if($this->prefer_https && !Request::secure())
            return redirect()->secure(Request::path());

        // if default hostname is loaded and this is not the default hostname
        if(Request::getHttpHost() != $this->hostname)
            return redirect()->away("http://{$this->hostname}/" . (Request::path() == '/' ? null : Request::path()));

        return null;
    }
}