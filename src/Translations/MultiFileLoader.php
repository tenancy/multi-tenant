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

namespace Hyn\Tenancy\Translations;

use Illuminate\Contracts\Translation\Loader;

class MultiFileLoader implements Loader
{
    /**
     * @var \Illuminate\Contracts\Translation\Loader[]
     */
    protected $loaders = [];

    /** @var array */
    protected $hints = [];

    /**
     * @param Loader $loader
     * @return $this
     */
    public function addLoader(Loader $loader)
    {
        $this->loaders[] = $loader;
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
        foreach ($this->loaders as $loader) {
            $results = array_merge($results, $loader->load($locale, $group, $namespace));
        }

        return $results;
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param  string $namespace
     * @param  string $hint
     * @return void
     */
    public function addNamespace($namespace, $hint)
    {
        foreach ($this->loaders as $loader) {
            $loader->addNamespace($namespace, $hint);
        }
        $this->hints[$namespace] = $hint;
    }

    /**
     * Add a new JSON path to the loader.
     *
     * @param  string $path
     * @return void
     */
    public function addJsonPath($path)
    {
        foreach ($this->loaders as $loader) {
            $loader->addJsonPath($path);
        }
    }

    /**
     * Get an array of all the registered namespaces.
     *
     * @return array
     */
    public function namespaces()
    {
        return $this->hints;
    }
}
