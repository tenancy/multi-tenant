<?php

namespace Hyn\MultiTenant\Tenant;

use Config;
use File;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Hyn\MultiTenant\Contracts\DirectoryContract;
use Hyn\MultiTenant\Models\Website;

/**
 * Class Directory.
 *
 * Helps with tenant directories
 * - cache
 * - views
 * - migrations
 * - media
 * - vendor
 * - lang
 */
class Directory implements DirectoryContract
{
    /**
     * @var array
     */
    protected $paths_to_create = ['base', 'config', 'views', 'lang', 'cache', 'image_cache', 'media', 'vendor'];

    /**
     * @var Website
     */
    protected $website;

    /**
     * Base tenant path.
     *
     * @var string
     */
    protected $base_path;

    /**
     * Old directory for base.
     *
     * @var string|null
     */
    protected $old_path;

    public function __construct(Website $website)
    {
        $this->website = $website;

        if ($this->website->isDirty('identifier')) {
            $this->old_path = sprintf('%s/%d-%s/',
                Config::get('multi-tenant.tenant-directory') ? Config::get('multi-tenant.tenant-directory') : storage_path('multi-tenant'),
                $this->website->id,
                $this->website->getOriginal('identifier'));
            if (! File::isDirectory($this->old_path)) {
                $this->old_path = null;
            }
        }

        $this->base_path = sprintf('%s/%d-%s/',
            Config::get('multi-tenant.tenant-directory') ? Config::get('multi-tenant.tenant-directory') : storage_path('multi-tenant'),
            $this->website->id,
            $this->website->identifier);
    }

    /**
     * Tenant config directory.
     *
     * @return string|null
     */
    public function config()
    {
        return $this->base() ? sprintf('%sconfig/', $this->base()) : null;
    }

    /**
     * Tenant views directory.
     *
     * @return string|null
     */
    public function views()
    {
        return $this->base() ? sprintf('%sviews/', $this->base()) : null;
    }

    /**
     * Tenant language/trans directory.
     *
     * @return string|null
     */
    public function lang()
    {
        return $this->base() ? sprintf('%slang/', $this->base()) : null;
    }

    /**
     * Tenant vendor directory.
     *
     * @return string|null
     */
    public function vendor()
    {
        return $this->base() ? sprintf('%svendor/', $this->base()) : null;
    }

    /**
     * Tenant cache directory.
     *
     * @return string|null
     */
    public function cache()
    {
        return $this->base() ? sprintf('%scache/', $this->base()) : null;
    }

    /**
     * Tenant image cache directory.
     *
     * @return null|string
     */
    public function image_cache()
    {
        return $this->cache() ? sprintf('%simage/', $this->cache()) : null;
    }

    /**
     * Tenant media directory.
     *
     * @return string|null
     */
    public function media()
    {
        return $this->base() ? sprintf('%smedia/', $this->base()) : null;
    }

    /**
     * Tenant base path.
     *
     * @return string|null
     */
    public function base()
    {
        return $this->base_path;
    }

    /**
     * Loads tenant providers.
     *
     * @return string
     */
    public function providers()
    {
        return $this->base() && File::exists($this->base().'providers.php') ? $this->base().'providers.php' : null;
    }

    /**
     * Old base path for tenant.
     *
     * @return null|string
     */
    public function old_base()
    {
        return $this->old_path;
    }

    /**
     * Register all available paths into the laravel system.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return Directory
     */
    public function registerPaths($app)
    {
        // only register if tenant directory exists
        if ($this->base()) {
            /*
             * critical priority, load vendors
             */
            if ($this->vendor() && File::exists($this->vendor().'autoload.php')) {
                File::requireOnce($this->vendor().'autoload.php');
            }
            /*
             * highest priority, load service providers; or possible custom code before any other include from tenant
             */
            if ($this->providers() && File::exists($this->providers())) {
                File::requireOnce($this->providers());
            }
            /*
             * mediocre priority, load additional config files
             */
            if ($this->config() && File::isDirectory($this->config())) {
                foreach (File::allFiles($this->config()) as $path) {
                    $key = File::name($path);
                    $app['config']->set($key, array_merge($app['config']->get($key, []), File::getRequire($path)));
                }
            }
            /*
             * lowest priority load view directory
             */
            if ($this->views() && File::isDirectory($this->views())) {
                $app['view']->addLocation($this->views());
            }

            // set cache
            if (File::isDirectory($this->cache())) {
                $app['config']->set('cache.prefix', "{$app['config']->get('cache.prefix')}-{$this->website->id}");
            }

            // @TODO we really can't use cache yet for application cache

            // replaces lang directory
            if ($this->lang() && File::isDirectory($this->lang())) {
                $path = $this->lang();

                $app->bindShared('translation.loader', function ($app) use ($path) {
                    return new FileLoader($app['files'], $path);
                });
                $app->bindShared('translator', function ($app) {
                    $translator = new Translator($app['translation.loader'], $app['config']['app.locale']);
                    $translator->setFallback($app['config']['app.fallback_locale']);

                    return $translator;
                });
            }
            // identify a possible routes.php file
            if ($this->routes()) {
                File::requireOnce($this->routes());
            }
        }

        return $this;
    }

    /**
     * Creates tenant directories.
     *
     * Creates all required tenant directories
     *
     * @return bool
     */
    public function create()
    {
        $done = 0;
        foreach ($this->paths_to_create as $i => $directory) {
            if (File::isDirectory($this->{$directory}()) || File::makeDirectory($this->{$directory}(), 0755, true)) {
                $done++;
            }
        }

        return $done == ($i + 1);
    }

    /**
     * Path to tenant routes.php.
     *
     * @return string|null
     */
    public function routes()
    {
        if ($this->base()) {
            $routes = sprintf('%sroutes.php', $this->base());
        }

        return $this->base() && File::exists($routes) ? $routes : null;
    }

    /**
     * Move from old to new path.
     *
     * @return bool
     */
    public function move()
    {
        if ($this->old_base()) {
            return File::move($this->old_base(), $this->base());
        }
    }

    /**
     * Delete directory.
     *
     * @return bool
     */
    public function delete()
    {
        return File::deleteDirectory($this->base());
    }

    /**
     * Paths that need to be created.
     *
     * @return array
     */
    public function pathsToCreate()
    {
        return $this->paths_to_create;
    }
}
