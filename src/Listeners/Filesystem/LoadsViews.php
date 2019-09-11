<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Listeners\Filesystem;

use Hyn\Tenancy\Abstracts\AbstractTenantDirectoryListener;
use Hyn\Tenancy\Abstracts\WebsiteEvent;
use Hyn\Tenancy\Exceptions\FilesystemException;
use Illuminate\Contracts\View\Factory;

class LoadsViews extends AbstractTenantDirectoryListener
{
    protected $configBaseKey = 'tenancy.folders.views';

    /**
     * @var string
     */
    protected $path = 'views';

    /**
     * @var Factory|\Illuminate\View\Factory
     */
    protected $views;
    protected $viewsPath;

    /**
     * @param WebsiteEvent $event
     * @throws FilesystemException
     */
    public function load(WebsiteEvent $event)
    {
        if ($this->directory()->isLocal()) {

            /** @var Factory views */
            $this->views = app(Factory::class);
            $this->viewsPath = $this->directory()->path($this->path, true);

            $namespace = $this->config->get('tenancy.folders.views.namespace');

            if ($namespace === null) {
                $this->addToGlobal($this->config->get('tenancy.folders.views.override-global'));
            } else {
                $this->addToNamespace($namespace);
            }
        } else {
            throw new FilesystemException("$this->path is not available locally, cannot include");
        }
    }

    protected function addToGlobal(bool $override)
    {
        if ($override) {
            $this->config->prepend('view.paths', $this->viewsPath);
            $this->views->getFinder()->prependLocation($this->viewsPath);

            // Needed to clear the views cache.
            $this->views->getFinder()->flush();
        } else {
            $this->views->addLocation($this->viewsPath);
        }
    }

    protected function addToNamespace(string $namespace)
    {
        $this->views->addNamespace($namespace, $this->viewsPath);
    }
}
