<?php

namespace Hyn\MultiTenant\Contracts;

interface DirectoryContract
{
    /**
     * Tenant config directory.
     *
     * @return string|null
     */
    public function config();

    /**
     * Tenant views directory.
     *
     * @return string|null
     */
    public function views();

    /**
     * Tenant language/trans directory.
     *
     * @return string|null
     */
    public function lang();

    /**
     * Tenant vendor directory.
     *
     * @return string|null
     */
    public function vendor();

    /**
     * Tenant cache directory.
     *
     * @return string|null
     */
    public function cache();

    /**
     * Tenant image cache directory.
     *
     * @return null|string
     */
    public function imageCache();

    /**
     * Tenant media directory.
     *
     * @return string|null
     */
    public function media();

    /**
     * Register all available paths into the laravel system.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return object
     */
    public function registerPaths($app);

    /**
     * Tenant base path.
     *
     * @return string|null
     */
    public function base();

    /**
     * Creates tenant directories.
     *
     * @return bool
     */
    public function create();

    /**
     * Path to tenant routes.php.
     *
     * @return string|null
     */
    public function routes();
}
