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
        $this->readConfigurationFiles($this->path);
    }

    /**
     * @param string $path
     */
    protected function readConfigurationFiles(string $path)
    {
        foreach ($this->directory->files($path) as $file) {

            $key = basename($file, '.php');

            $values = $this->directory->getRequire($file);

            $existing = $this->config->get($key, []);

            $this->config->set($key, array_merge(
                $existing,
                $values
            ));
        }
    }
}
