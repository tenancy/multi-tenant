<?php

namespace Hyn\Tenancy\Listeners\Filesystem;

use Hyn\Tenancy\Abstracts\AbstractTenantDirectoryListener;
use Hyn\Tenancy\Events\Hostnames\Identified;

class LoadsConfigs extends AbstractTenantDirectoryListener
{
    protected $configBaseKey = 'tenancy.folders.config';

    /**
     * @var string
     */
    protected $path = 'config';

    /**
     * @param Identified $event
     */
    public function load(Identified $event)
    {
        if ($this->exists($event->hostname->website)) {
            $this->readConfigurationFiles($this->path($this->hostname->website));
        }
    }

    protected function readConfigurationFiles(string $path)
    {
        foreach ($this->filesystem->allFiles($this->path()) as $file) {
            dd($file);


//            $this->config->set()
        }

//        $config = $this->app['config']->get($key, []);
//        $this->app['config']->set($key, array_merge(require $path, $config));
    }
}
