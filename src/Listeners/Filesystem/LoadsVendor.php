<?php

namespace Hyn\Tenancy\Listeners\Filesystem;

use Hyn\Tenancy\Abstracts\AbstractTenantDirectoryListener;
use Hyn\Tenancy\Events\Hostnames\Identified;
use Hyn\Tenancy\Exceptions\FilesystemException;

class LoadsVendor extends AbstractTenantDirectoryListener
{
    protected $configBaseKey = 'tenancy.folders.vendor';

    /**
     * @var string
     */
    protected $path = 'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

    /**
     * @param Identified $event
     * @throws FilesystemException
     */
    public function load(Identified $event)
    {
        if ($this->directory->isLocal()) {
            $this->directory->requireOnce($this->path);
        } else {
            throw new FilesystemException("$this->path is not available locally, cannot include");
        }
    }
}
