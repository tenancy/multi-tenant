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

namespace Hyn\Tenancy\Services;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;

class ConfigurationLoader extends LoadConfiguration
{
    public function reset(Application $app)
    {
        return $this->items($app);
    }

    protected function items(Application $app)
    {
        $cached = $app->getCachedConfigPath();

        if (file_exists($cached)) {
            return require $cached;
        }

        $items = [];

        foreach ($this->getConfigurationFiles($app) as $key => $file) {
            $items[$key] = require $file;
        }

        return $items;
    }
}
