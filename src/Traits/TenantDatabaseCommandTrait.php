<?php

namespace Hyn\MultiTenant\Traits;

use Hyn\MultiTenant\Contracts\WebsiteRepositoryContract;
use Symfony\Component\Console\Input\InputOption;

trait TenantDatabaseCommandTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getWebsitesFromOption()
    {
        $repository = app(WebsiteRepositoryContract::class);

        if ($this->option('tenant') == 'all') {
            return $repository->all();
        } else {
            return $repository
                ->queryBuilder()
                ->whereIn('id', explode(',', $this->option('tenant')))
                ->get();
        }
    }

    /**
     * @return array
     */
    protected function getTenantOption()
    {
        return [['tenant', null, InputOption::VALUE_OPTIONAL, 'The tenant(s) to apply on; use {all|5,8}']];
    }
}
