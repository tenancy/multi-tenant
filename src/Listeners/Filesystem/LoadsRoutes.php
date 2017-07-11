<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Listeners\Filesystem;

use Hyn\Tenancy\Abstracts\AbstractTenantDirectoryListener;
use Hyn\Tenancy\Events\Hostnames\Identified;
use Hyn\Tenancy\Exceptions\FilesystemException;
use Illuminate\Routing\Router;

class LoadsRoutes extends AbstractTenantDirectoryListener
{
    protected $configBaseKey = 'tenancy.folders.routes';

    /**
     * @var string
     */
    protected $path = 'routes.php';

    /**
     * @param Identified $event
     * @throws FilesystemException
     */
    public function load(Identified $event)
    {
        if ($this->directory->isLocal()) {
            $this->loadRoutes($this->path);
        } else {
            throw new FilesystemException("$this->path is not available locally, cannot include");
        }
    }

    /**
     * @param $path
     */
    public function loadRoutes($path)
    {
        /** @var Router $router */
        $router = app('router');

        $prefix = $this->config->get('tenancy.folders.routes.prefix', '');

        $router->group(
            $prefix ? compact('prefix') : [],
            function ($router) use ($path) {
                return $this->directory->getRequire($path);
            }
        );
    }
}
