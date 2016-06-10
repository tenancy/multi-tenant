<?php

namespace Hyn\MultiTenant\Tenant;

use File;
use Hyn\MultiTenant\Contracts\DirectoryContract;
use Hyn\MultiTenant\Models\Website;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;

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
                config('multi-tenant.tenant-directory') ? config('multi-tenant.tenant-directory') : storage_path('multi-tenant'),
                $this->website->id,
                $this->website->getOriginal('identifier'));
            if (! File::isDirectory($this->old_path)) {
                $this->old_path = null;
            }
        }

        $this->base_path = sprintf('%s/%d-%s/',
            config('multi-tenant.tenant-directory') ? config('multi-tenant.tenant-directory') : storage_path('multi-tenant'),
            $this->website->id,
            $this->website->identifier);
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
     * Tenant cache directory.
     *
     * @return string|null
     */
    public function cache()
    {
        return $this->base() ? sprintf('%scache/', $this->base()) : null;
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
     * Tenant media directory.
     *
     * @return string|null
     */
    public function media()
    {
        return $this->base() ? sprintf('%smedia/', $this->base()) : null;
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
            if (! $this->disallowed('vendor') && $this->vendor() && File::exists($this->vendor().'autoload.php')) {
                File::requireOnce($this->vendor().'autoload.php');
            }
            /**
             * critical priority, load .env
             */
            if (! $this->disallowed('env') && $this->env()) {
                $app->useEnvironmentPath($this->env());
            }
            /*
             * highest priority, load service providers; or possible custom code before any other include from tenant
             */
            if (! $this->disallowed('providers') && $this->providers() && File::exists($this->providers())) {
                File::requireOnce($this->providers());
            }
            /*
             * mediocre priority, load additional config files
             */
            if (! $this->disallowed('config') && $this->config() && File::isDirectory($this->config())) {
                foreach (File::allFiles($this->config()) as $path) {
                    $key = File::name($path);
                    $app['config']->set($key, array_merge($app['config']->get($key, []), File::getRequire($path)));
                }
            }
            /*
             * lowest priority load view directory
             */
            if (! $this->disallowed('views') && $this->views() && File::isDirectory($this->views())) {
                $app['view']->addLocation($this->views());
            }

            // set cache
            if (File::isDirectory($this->cache())) {
                $app['config']->set('cache.prefix', "{$app['config']->get('cache.prefix')}-{$this->website->id}");
            }

            // replaces lang directory
            if (! $this->disallowed('lang') && $this->lang() && File::isDirectory($this->lang())) {
                $path = $this->lang();

                $app->singleton('translation.loader', function ($app) use ($path) {
                    return new FileLoader($app['files'], $path);
                });
                $app->singleton('translator', function ($app) {
                    $translator = new Translator($app['translation.loader'], $app['config']['app.locale']);
                    $translator->setFallback($app['config']['app.fallback_locale']);

                    return $translator;
                });
            }
            // identify a possible routes.php file
            if (! $this->disallowed('routes') && $this->routes()) {
                File::requireOnce($this->routes());
            }
        }

        return $this;
    }

    /**
     * Check whether a specific functionality is disabled globally.
     *
     * @param $type
     *
*@return bool
     */
    protected function disallowed($type)
    {
        return config('multi-tenant.disallow-for-tenant.' . $type, false);
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
     * Loads tenant .env file path.
     *
     * @return null|string
     */
    public function env()
    {
        return $this->base() && File::exists($this->base() . '.env') ? $this->base() : null;
    }

    /**
     * Loads tenant providers.
     *
     * @return string
     */
    public function providers()
    {
        return $this->base() && File::exists($this->base() . 'providers.php') ? $this->base() . 'providers.php' : null;
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
     * Old base path for tenant.
     *
     * @return null|string
     */
    public function old_base()
    {
        return $this->old_path;
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
