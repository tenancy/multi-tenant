<?php namespace HynMe\MultiTenant\Tenant;

use Config, File;
use HynMe\MultiTenant\Contracts\DirectoryContract;
use HynMe\MultiTenant\Models\Hostname;
use Illuminate\Support\ClassLoader;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;

/**
 * Class Directory
 *
 * Helps with tenant directories
 * - cache
 * - views
 * - migrations
 * - media
 * - vendor
 * - lang
 *
*@package HynMe\MultiTenant\Tenant
 */
class Directory implements DirectoryContract
{
    /**
     * @var Hostname
     */
    protected $hostname;

    /**
     * Base tenant path
     * @var string
     */
    protected $base_path;

    public function __construct(Hostname $hostname)
    {
        $this->hostname = $hostname;

        $this->base_path = sprintf("%s/%d/",
            Config::get('multi-tenant.tenant-directory') ? Config::get('multi-tenant.tenant-directory') : storage_path('multi-tenant'),
            $this->hostname->website_id);

        // check the directory, otherwise unset
        if(!File::isDirectory($this->base_path))
            $this->base_path = null;
    }


    /**
     * Tenant config directory
     *
     * @return string|null
     */
    public function config()
    {
        return $this->base() ? sprintf("%s/config/", $this->base()) : null;
    }
    /**
     * Tenant views directory
     *
     * @return string|null
     */
    public function views()
    {
        return $this->base() ? sprintf("%s/views/", $this->base()) : null;
    }

    /**
     * Tenant language/trans directory
     *
     * @return string|null
     */
    public function lang()
    {
        return $this->base() ? sprintf("%s/lang/", $this->base()) : null;
    }

    /**
     * Tenant vendor directory
     *
     * @return string|null
     */
    public function vendor()
    {
        return $this->base() ? sprintf("%s/vendor/", $this->base()) : null;
    }

    /**
     * Tenant cache directory
     *
     * @return string|null
     */
    public function cache()
    {
        return $this->base() ? sprintf("%s/cache/", $this->base()) : null;
    }

    /**
     * Tenant media directory
     *
     * @return string|null
     */
    public function media()
    {
        return $this->base() ? sprintf("%s/media/", $this->base()) : null;
    }

    /**
     * Tenant base path
     *
     * @return string|null
     */
    public function base()
    {
        return $this->base_path;
    }

    /**
     * Register all available paths into the laravel system
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @return Directory
     */
    public function registerPaths($app)
    {
        // only register if tenant directory exists
        if($this->base())
        {
            // adds views in base namespace
            if($this->views())
                $app['view']->addLocation($this->views());
            // merges overruling config files
            if($this->config()) {
                foreach (File::allFiles($this->config()) as $path) {
                    $key = File::name($path);
                    $app['config']->set($key, array_merge(require $path, $app['config']->get($key, [])));
                }
            }
            // add additional vendor directory
            if($this->vendor())
                ClassLoader::addDirectories([$this->vendor()]);

            // set cache
            $app['config']->set('cache.prefix', "{$app['config']->get('cache.prefix')}-{$this->hostname->website_id}");
            // @TODO we really can't use cache yet for application cache

            // replaces lang directory
            if($this->lang()) {
                $path = $this->lang();

                $app->bindShared('translation.loader', function($app) use ($path)
                {
                    return new FileLoader($app['files'], $path);
                });
                $app->bindShared('translator', function($app)
                {
                    return (new Translator($app['translation.loader'], $app['config']['app.locale']))->setFallback($app['config']['app.fallback_locale']);
                });
            }
            // identify a possible routes.php file
            if($this->routes())
                File::requireOnce($this->routes());
        }
        return $this;
    }

    /**
     * Creates tenant directories
     *
     * Creates all required tenant directories
     * @return void
     */
    public function create()
    {
        foreach(['base', 'views', 'lang', 'cache', 'media', 'vendor'] as $directory)
        {
            File::makeDirectory($this->{$directory}(), 0755, true);
        }
    }


    /**
     * Path to tenant routes.php
     *
     * @return string|null
     */
    public function routes()
    {
        if($this->base())
            $routes = sprintf("%sroutes.php", $this->base());

        return $this->base() && File::exists($routes) ? $routes : null;
    }
}