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
use Hyn\Tenancy\Abstracts\HostnameEvent;
use Hyn\Tenancy\Exceptions\FilesystemException;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;

class LoadsTranslations extends AbstractTenantDirectoryListener
{
    /**
     * @var string
     */
    protected $configBaseKey = 'tenancy.folders.trans';

    /**
     * @var string
     */
    protected $path = 'lang';

    /**
     * @param HostnameEvent $event
     * @throws FilesystemException
     */
    public function load(HostnameEvent $event)
    {
        if ($this->directory->isLocal()) {
            $this->readLanguageFiles($this->directory->path($this->path, true));
        } else {
            throw new FilesystemException("$this->path is not available locally, cannot include");
        }
    }

    /**
     * @param string $path
     */
    protected function readLanguageFiles(string $path)
    {
        if ($this->config->get('tenancy.folders.trans.override-global')) {
            app()->singleton('translation.loader', function ($app) use ($path) {
                return new FileLoader($app['files'], $path);
            });
            app()->singleton('translator', function ($app) {
                $translator = new Translator($app['translation.loader'], $app['config']['app.locale']);
                $translator->setFallback($app['config']['app.fallback_locale']);
                return $translator;
            });
        } elseif ($namespace = $this->config->get('tenancy.folders.trans.namespace')) {
            app('translator')->addNamespace($namespace, $path);
        }
    }
}
