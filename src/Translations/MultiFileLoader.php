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

class MultiFileLoader extends FileLoader
{
    /**
     * @var FileLoader
     */
    protected $fileLoaders = [];

    /**
     * @param FileLoader $loader
     * @return $this
     */
    public function addLoader(FileLoader $loader)
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
