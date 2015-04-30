<?php namespace HynMe\MultiTenant\Contracts;

interface DirectoryContract
{
    /**
     * Tenant config directory
     *
     * @return string|null
     */
    public function config();
    /**
     * Tenant views directory
     *
     * @return string|null
     */
    public function views();

    /**
     * Tenant language/trans directory
     *
     * @return string|null
     */
    public function lang();

    /**
     * Tenant vendor directory
     *
     * @return string|null
     */
    public function vendor();

    /**
     * Tenant cache directory
     *
     * @return string|null
     */
    public function cache();

    /**
     * Tenant media directory
     *
     * @return string|null
     */
    public function media();

    /**
     * Register all available paths into the laravel system
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @return object
     */
    public function registerPaths($app);

    /**
     * Tenant base path
     *
     * @return string|null
     */
    public function base();

    /**
     * Creates tenant directories
     *
     * @return void
     */
    public function create();
}