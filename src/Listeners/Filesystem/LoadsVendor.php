<?php

namespace Hyn\Tenancy\Listeners\Filesystem;

use Hyn\Tenancy\Abstracts\AbstractTenantDirectoryListener;
use Hyn\Tenancy\Events\Hostnames\Identified;

class LoadsVendor extends AbstractTenantDirectoryListener
{
    protected $configBaseKey = 'tenancy.folders.vendor';

    /**
     * @var string
     */
    protected $path = 'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

    /**
     * @param Identified $event
     */
    public function load(Identified $event)
    {
        if ($this->exists($event->hostname->website)) {
            require_once $this->path($event->hostname->website);
        }
    }
}
