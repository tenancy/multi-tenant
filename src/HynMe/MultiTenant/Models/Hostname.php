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
    protected $fillable = ['website_id', 'hostname'];

    /**
     * @return null|Website
     */
    public function website()
    {
        return $this->belongsTo(__NAMESPACE__.'\Website');
    }

    /**
     * Host to redirect to
     * @return null|Hostname
     */
    public function redirectToHostname()
    {
        return $this->belongsTo(__CLASS__, 'redirect_to');
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