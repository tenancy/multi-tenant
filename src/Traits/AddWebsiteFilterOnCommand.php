<?php

namespace Hyn\Tenancy\Traits;

use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Input\InputOption;

trait AddWebsiteFilterOnCommand
{
    protected function processHandle($callable)
    {
        $query = $this->websites->query();

        if ($this->option('website_id')) {
            $query->whereIn('id', $this->option('website_id'));
        }

        $query->orderBy('id')->chunk(10, function (Collection $websites) use ($callable) {
            $websites->each($callable);
        });
    }

    protected function addWebsiteOption()
    {
        return ['website_id', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'The tenancy website_ids (not uuid) to migrate specifically.', null];
    }
}
