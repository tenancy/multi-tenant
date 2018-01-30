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

namespace Hyn\Tenancy\Translations;

use Illuminate\Translation\FileLoader;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Filesystem\Filesystem;

class MultiFileLoader extends FileLoader
{
    /**
     * @var \Illuminate\Translation\FileLoader
     */
    protected $fileLoaders = [];

    /**
     * Create a new file loader instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $path
     * @return void
     */
    public function __construct(Filesystem $files, $path)
    {
        parent::__construct($files, $path);
    }

    /**
     * @param \Illuminate\Translation\FileLoader $loader
     */
    public function addLoader(\Illuminate\Translation\FileLoader $loader)
    {
        $this->fileLoaders[] = $loader;
        return $this;
    }

    /**
     * Load the messages for the given locale.
     *
     * @param  string  $locale
     * @param  string  $group
     * @param  string  $namespace
     * @return array
     */
    public function load($locale, $group, $namespace = null)
    {
        $results = [];
        foreach ($this->fileLoaders as $loader) {
            $results = array_merge($results, $loader->load($locale, $group, $namespace));
        }

        return $results;
    }
}
