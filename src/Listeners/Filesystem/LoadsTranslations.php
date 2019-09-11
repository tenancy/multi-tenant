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
use Hyn\Tenancy\Translations\MultiFileLoader;
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
     * @param WebsiteEvent $event
     * @throws FilesystemException
     */
    public function load(WebsiteEvent $event)
    {
        if ($this->directory()->isLocal()) {
            $this->readLanguageFiles($this->directory()->path($this->path, true));
        } else {
            throw new FilesystemException("$this->path is not available locally, cannot include");
        }
    }

    /**
     * @param string $path
     */
    protected function readLanguageFiles(string $path)
    {
        if (config('tenancy.folders.trans.override-global')) {
            app()->extend('translation.loader', function ($loader) use ($path) {
                if ($loader instanceof MultiFileLoader) {
                    return $loader->addLoader(new FileLoader(app()->make('files'), $path));
                }

                $multiLoader = new MultiFileLoader(app()->make('files'), $path);
                $multiLoader->addLoader($loader);
                $multiLoader->addLoader(new FileLoader(app()->make('files'), $path));
                return $multiLoader;
            });
            app()->singleton('translator', function ($app) {
                $translator = new Translator($app['translation.loader'], $app['config']['app.locale']);
                $translator->setFallback($app['config']['app.fallback_locale']);
                return $translator;
            });
        } elseif ($namespace = config('tenancy.folders.trans.namespace')) {
            app('translator')->addNamespace($namespace, $path);
        }
    }
}
