<?php

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
