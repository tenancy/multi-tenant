<?php

namespace Hyn\Tenancy\Tenant;

use File;
use Hyn\Tenancy\Contracts\DirectoryContract;
use Hyn\Tenancy\Models\Website;
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
            if (!File::isDirectory($this->old_path)) {
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
        if ($this->base() && !$this->disallowed('base')) {
            $this->loadVendor();
            $this->loadProviders();
            $this->loadConfigs($app);
            $this->loadViews($app);
            $this->loadCache($app);
            $this->loadTranslations($app);
            $this->loadRoutes();
            $this->addTenantDisk($app);
        }

        return $this;
    }

    /**
     * Check whether a specific functionality is disabled globally.
     *
     * @param $type
     *
     * @return bool
     */
    protected function disallowed($type)
    {
        return config('multi-tenant.disallow-for-tenant.' . $type, false);
    }

    /**
     * critical priority, load vendors
     */
    protected function loadVendor()
    {
        if (!$this->disallowed('vendor') && $this->vendor() && File::exists($this->vendor() . 'autoload.php')) {
            File::requireOnce($this->vendor() . 'autoload.php');
        }
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
     * highest priority, load service providers; or possible custom code before any other include from tenant
     */
    protected function loadProviders()
    {
        if (!$this->disallowed('providers') && $this->providers() && File::exists($this->providers())) {
            File::requireOnce($this->providers());
        }
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
     * mediocre priority, load additional config files
     *
     * @param $app
     */
    protected function loadConfigs($app)
    {
        if (!$this->disallowed('config') && $this->config() && File::isDirectory($this->config())) {
            foreach (File::allFiles($this->config()) as $path) {
                $key = File::name($path);
                $app['config']->set($key, array_merge($app['config']->get($key, []), File::getRequire($path)));
            }
        }
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
     * Lowest priority load view directory.
     *
     * @param $app
     */
    protected function loadViews($app)
    {
        if (!$this->disallowed('views') && $this->views() && File::isDirectory($this->views())) {
            $app['view']->addLocation($this->views());
        }
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
     * Set tenant cache.
     *
     * @param $app
     */
    protected function loadCache($app)
    {
        if (File::isDirectory($this->cache())) {
            $app['config']->set('cache.prefix', "{$app['config']->get('cache.prefix')}-{$this->website->id}");
        }
    }

    /**
     * Replaces lang directory.
     *
     * @param $app
     */
    protected function loadTranslations($app)
    {
        if (!$this->disallowed('lang') && $this->lang() && File::isDirectory($this->lang())) {
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
     * identify a possible routes.php file
     */
    protected function loadRoutes()
    {
        if (!$this->disallowed('routes') && $this->routes()) {
            File::requireOnce($this->routes());
        }
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

        if ($this->disallowed('base')) {
            return false;
        }

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

    /**
     * Setup local disk for tenant media
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function addTenantDisk($app) {
        // Set up local disk
        $app['config']->set('filesystems.disks.tenant', ['driver' => 'local', 'root' => $this->media()]);
    }
}
